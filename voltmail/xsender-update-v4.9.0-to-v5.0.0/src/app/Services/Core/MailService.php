<?php

namespace App\Services\Core;

use App\Enums\SettingKey;
use App\Enums\StatusEnum;
use Illuminate\Support\Arr;

class MailService
{
     ## Email Verification Check
     
     /**
      * verifyEmail
      *
      * @param string $email
      * 
      * @return array
      */
     public function verifyEmail(string $email): array {

          $result = [
               'valid' => false,
               'checks' => [],
               'suggested_email' => null,
               'reason' => null
          ];

          $rawChecks = site_settings(SettingKey::VERIFY_EMAIL_ADDITIONAL_CHECKS->value);
          $emailAdditionalChecks = $rawChecks ? json_decode($rawChecks, true) : [
               'invalid_syntax' => StatusEnum::TRUE->status(),
               'invalid_domain' => StatusEnum::TRUE->status(),
          ];
          
          if (Arr::get($emailAdditionalChecks, 'invalid_syntax') == StatusEnum::TRUE->status()) {
               
               if (!preg_match('/^[_a-zA-Z0-9-]+(\.[_a-zA-Z0-9-]+)*@[a-zA-Z0-9-]+(\.[a-zA-Z0-9-]+)*(\.[a-zA-Z]{2,})$/', $email)) {
                    
                    $result['reason'] = Arr::get($emailAdditionalChecks, 'invalid_syntax_message');
                    return $result;
               }
               $result['checks']['syntax'] = true;
          }
 
          list($localPart, $domain) = explode('@', $email);
 
          if (Arr::get($emailAdditionalChecks, 'invalid_domain') == StatusEnum::TRUE->status()) {
               
               if (!checkdnsrr($domain, 'MX') && !checkdnsrr($domain, 'A')) {
                    $result['reason'] = Arr::get($emailAdditionalChecks, 'invalid_domain_message');
                    return $result;
               }
               $result['checks']['mx'] = true;
          }

          if (Arr::get($emailAdditionalChecks, 'disposable_domain') == StatusEnum::TRUE->status()) {
          
               $disposableDomainList = site_settings(SettingKey::DISPOSABLE_DOMAIN_LIST->value);
               $disposableDomains  = $disposableDomainList 
                                        ? json_decode($disposableDomainList, true) 
                                        : [];
               
               if (in_array($domain, $disposableDomains)) {
                    $result['reason'] = Arr::get($emailAdditionalChecks, 'disposable_domain_message');
                    $result['checks']['disposable'] = false;
                    return $result;
               }
               $result['checks']['disposable'] = true;
          }
 
          if (Arr::get($emailAdditionalChecks, 'domain_typos') == StatusEnum::TRUE->status()) {

               $commonDomainList = site_settings(SettingKey::COMMON_DOMAIN->value);
               $commonDomains = $commonDomainList ? json_decode($commonDomainList, true) : [];
               $suggestedDomain = collect($commonDomains)
                                        ->first(function ($domainData) use ($domain) {
                                             return in_array($domain, Arr::get($domainData, 'typo', []));
                                        });

               if ($suggestedDomain) {
                    $result['suggested_email'] = $localPart . '@' . Arr::get($suggestedDomain, 'name.0', $domain);
                    $result['checks']['typo'] = false;
                    $result['reason'] = Arr::get($emailAdditionalChecks, 'domain_typo_message', 'Possible typo detected');
               } else {
                    $result['checks']['typo'] = true;
               }
          }
 
          if (Arr::get($emailAdditionalChecks, 'role_based_email') == StatusEnum::TRUE->status()) {

               $commonRoleList     = site_settings(SettingKey::EMAIL_ROLE_LIST->value);
               $commonRoles        = $commonRoleList 
                                        ? json_decode($commonRoleList, true) 
                                        : [];
     
               if (in_array(strtolower($localPart), (array) $commonRoles)) {
                    $result['reason'] = Arr::get($emailAdditionalChecks, 'role_based_message');
                    return $result;
               }
          }
 
          if (Arr::get($emailAdditionalChecks, 'check_tld') == StatusEnum::TRUE->status()) {

               $suspiciousTLDList  = site_settings(SettingKey::TLD_LIST->value);
               $suspiciousTLDs     = $suspiciousTLDList 
                                        ? json_decode($suspiciousTLDList, true) 
                                        : [];
     
               $hasSuspiciousTLD = collect($suspiciousTLDs)
                                        ->contains(function ($tld) use ($domain) {
                                             return str_ends_with($domain, $tld);
                                        });
     
               if ($hasSuspiciousTLD) {
                    $result['reason'] = Arr::get($emailAdditionalChecks, 'tld_message');
                    return $result;
               }
          }

          // Live SMTP Handshake check (HELO -> MAIL FROM -> RCPT TO)
          if (!empty($result['checks']['syntax']) && !empty($result['checks']['mx'])) {
               $smtpResult = $this->verifySmtpMailbox($domain, $email);
               $result['smtp'] = $smtpResult;

               if ($smtpResult['status'] === 'undeliverable') {
                    $result['checks']['mailbox'] = false;
                    $result['reason'] = $smtpResult['response'] ?: translate('Recipient mailbox does not exist on destination server (User Unknown)');
               } elseif ($smtpResult['status'] === 'deliverable') {
                    $result['checks']['mailbox'] = true;
               } elseif ($smtpResult['status'] === 'catch_all') {
                    $result['checks']['mailbox'] = true;
                    $result['is_catch_all'] = true;
               } else {
                    // skipped / greylisted / port 25 timeout: fallback to DNS verification
                    $result['checks']['mailbox'] = true;
               }
          }

          $result['valid'] = collect($result['checks'])->every(function ($value) {
               return $value === true;
          });
     
          return $result;
     }

     /**
      * Perform socket-level SMTP handshake (EHLO -> MAIL FROM -> RCPT TO)
      * to verify whether the specific inbox actually exists on the recipient mail server.
      */
     public function verifySmtpMailbox(string $domain, string $email, float $timeout = 3.0): array
     {
          $result = [
               'status'    => 'unknown', // deliverable, undeliverable, catch_all, greylisted, skipped
               'code'      => null,
               'response'  => null,
               'catch_all' => false,
          ];

          // 1. Resolve MX records sorted by priority
          $mxHosts = [];
          if (getmxrr($domain, $hosts, $weights)) {
               $combined = array_combine($hosts, $weights);
               asort($combined);
               $mxHosts = array_keys($combined);
          }

          if (empty($mxHosts)) {
               $mxHosts[] = $domain;
          }

          $primaryMx = $mxHosts[0];

          try {
               $socket = @fsockopen($primaryMx, 25, $errno, $errstr, $timeout);
               if (!$socket) {
                    $result['status'] = 'skipped';
                    $result['response'] = "Connection to {$primaryMx}:25 timed out or socket blocked";
                    return $result;
               }

               stream_set_timeout($socket, (int) ceil($timeout));

               $banner = $this->readSmtpResponse($socket);
               if (!str_starts_with($banner, '220')) {
                    fclose($socket);
                    $result['status'] = 'skipped';
                    return $result;
               }

               $heloHost = parse_url(config('app.url', 'reach.neuravolt.cloud'), PHP_URL_HOST) ?: 'reach.neuravolt.cloud';
               fputs($socket, "EHLO {$heloHost}\r\n");
               $heloResp = $this->readSmtpResponse($socket);
               if (!str_starts_with($heloResp, '250')) {
                    fputs($socket, "HELO {$heloHost}\r\n");
                    $heloResp = $this->readSmtpResponse($socket);
               }

               fputs($socket, "MAIL FROM: <verify@{$heloHost}>\r\n");
               $mailResp = $this->readSmtpResponse($socket);

               // Test actual recipient existence
               fputs($socket, "RCPT TO: <{$email}>\r\n");
               $rcptResp = $this->readSmtpResponse($socket);
               $code = (int) substr(trim($rcptResp), 0, 3);
               $result['code'] = $code;
               $result['response'] = trim($rcptResp);

               if ($code === 250) {
                    // Check for Catch-All: test random non-existent hash
                    $randomUser = 'chk_catchall_' . bin2hex(random_bytes(4));
                    fputs($socket, "RCPT TO: <{$randomUser}@{$domain}>\r\n");
                    $catchallResp = $this->readSmtpResponse($socket);
                    $catchCode = (int) substr(trim($catchallResp), 0, 3);

                    if ($catchCode === 250) {
                         $result['status'] = 'catch_all';
                         $result['catch_all'] = true;
                    } else {
                         $result['status'] = 'deliverable';
                    }
               } elseif (in_array($code, [550, 551, 552, 553, 554])) {
                    $result['status'] = 'undeliverable';
               } else {
                    $result['status'] = 'greylisted';
               }

               fputs($socket, "QUIT\r\n");
               fclose($socket);

          } catch (\Throwable $e) {
               $result['status'] = 'skipped';
               $result['response'] = $e->getMessage();
          }

          return $result;
     }

     /**
      * Read multiline RFC 5321 SMTP response from socket
      */
     protected function readSmtpResponse($socket): string
     {
          $response = '';
          while (!feof($socket)) {
               $line = fgets($socket, 1024);
               if ($line === false) break;
               $response .= $line;
               if (isset($line[3]) && $line[3] === ' ') {
                    break;
               }
               if (strlen(trim($line)) <= 3) {
                    break;
               }
          }
          return trim($response);
     }

     /**
      * processMailVerificationMessage
      *
      * @param array $data
      * 
      * @return string
      */
     public function processMailVerificationMessage(array $data): string {

          $status         = Arr::get($data, "valid", false);
          $suggestedEmail = Arr::get($data, "suggested_email");
          $reason         = $suggestedEmail 
                              ? Arr::get($data, "reason") . translate(". Suggested Email: "). $suggestedEmail
                              : Arr::get($data, "reason");
          return $status 
                    ? translate('Successfully checked Email Address Validity') 
                    : ($reason 
                         ? translate('The email address is invalid or cannot receive emails. Reason: ').$reason
                         : translate('The email address is invalid or cannot receive emails.'));
     }
}