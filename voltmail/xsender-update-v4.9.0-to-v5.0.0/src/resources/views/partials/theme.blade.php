@php
    $primary_hex   = (site_settings('primary_color')) ?: '#fc5a12';
    $primary_text_hex = (site_settings('primary_text_color')) ?: '#ffffff';
    $primary_light = hexToRgba($primary_hex);
    $secondary_hex = (site_settings('secondary_color')) ?: '#e64a19';
    $trinaryColor  = (site_settings('trinary_color')) ?: '#ffa040';
@endphp
<style>

:root {
  --color-primary: {{$primary_hex }} !important;
  --color-primary-text: {{$primary_text_hex }} !important;
  --color-primary-light: {{$primary_light }} !important;
  --color-secondary: {{$secondary_hex }} !important;
  --color-trinary: {{$trinaryColor }} !important;
  --color-gradient: linear-gradient(
    102deg,
    {{$trinaryColor }} 5.12%,
    {{$primary_hex }} 53.96%,
    {{$secondary_hex }} 90.8%
  );
  --text-gradient: linear-gradient(
    102deg,
    {{$trinaryColor }} 5.12%,
    {{$primary_hex }} 53.96%,
    {{$secondary_hex }} 90.8%
  );
}
</style>
