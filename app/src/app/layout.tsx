"use client";

import React, { useState, useEffect } from "react";
import { useRouter } from "next/navigation";
import "./globals.css";
import ApiClient from "../lib/api";
import { 
  Zap, 
  LayoutDashboard, 
  IndianRupee, 
  Settings, 
  LogOut, 
  Lock, 
  Mail, 
  User, 
  Building,
  ShieldAlert,
  Info
} from "lucide-react";

export default function RootLayout({
  children,
}: {
  children: React.ReactNode;
}) {
  const router = useRouter();
  const [isAuthenticated, setIsAuthenticated] = useState<boolean>(false);
  const [isLoginView, setIsLoginView] = useState<boolean>(true);
  const [activeTab, setActiveTab] = useState<string>("dashboard");
  
  // Auth Form State
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [name, setName] = useState("");
  const [company, setCompany] = useState("");
  const [plan, setPlan] = useState("BASIC");
  
  const [error, setError] = useState("");
  const [info, setInfo] = useState("");
  const [loading, setLoading] = useState(false);

  useEffect(() => {
    if (typeof window !== "undefined") {
      const token = localStorage.getItem("nv_user_token");
      if (token) {
        setIsAuthenticated(true);
      }
      
      const path = window.location.pathname;
      if (path.includes("/billing")) setActiveTab("billing");
      else if (path.includes("/settings")) setActiveTab("settings");
      else setActiveTab("dashboard");
    }
  }, []);

  const handleLogin = async (e: React.FormEvent) => {
    e.preventDefault();
    setError("");
    setInfo("");
    setLoading(true);

    try {
      const data = await ApiClient.post<{ token: string; user: { name: string; email: string; plan: string; status: string } }>("/auth/login", { email, password });

      localStorage.setItem("nv_user_token", data.token);
      localStorage.setItem("nv_user_name", data.user.name);
      localStorage.setItem("nv_user_email", data.user.email);
      localStorage.setItem("nv_user_plan", data.user.plan);
      localStorage.setItem("nv_user_status", data.user.status);

      setIsAuthenticated(true);
      window.location.href = "/";
    } catch (err: any) {
      setError(err.message || "Invalid credentials");
    } finally {
      setLoading(false);
    }
  };

  const handleSignup = async (e: React.FormEvent) => {
    e.preventDefault();
    setError("");
    setInfo("");
    setLoading(true);

    try {
      await ApiClient.post("/auth/signup", { email, password, name, company, plan });

      setInfo("Account registered! Your access is pending administrator approval.");
      setIsLoginView(true);
      setEmail("");
      setPassword("");
    } catch (err: any) {
      setError(err.message || "Registration failed");
    } finally {
      setLoading(false);
    }
  };

  const handleLogout = () => {
    localStorage.removeItem("nv_user_token");
    localStorage.removeItem("nv_user_name");
    localStorage.removeItem("nv_user_email");
    localStorage.removeItem("nv_user_plan");
    localStorage.removeItem("nv_user_status");
    setIsAuthenticated(false);
    window.location.href = "/";
  };

  const navigateTo = (tab: string, path: string) => {
    setActiveTab(tab);
    router.push(path);
  };

  if (!isAuthenticated) {
    return (
      <html lang="en">
        <body>
          <div style={{ display: "flex", alignItems: "center", justifyContent: "center", minHeight: "100vh", padding: "20px" }}>
            <div className="glass-card" style={{ width: "100%", maxWidth: "440px", padding: "40px 30px" }}>
              <div style={{ display: "flex", flexDirection: "column", alignItems: "center", gap: "8px", justifyContent: "center", marginBottom: "28px" }}>
                <img 
                  src="https://neuravolt.cloud/assets/logos/neuravolt_cloud_logo.png" 
                  alt="NeuraVolt Cloud Services" 
                  style={{ height: "48px", maxWidth: "240px", objectFit: "contain" }} 
                />
                <p style={{ fontSize: "0.78rem", color: "#64748B", fontWeight: "600", textTransform: "uppercase", letterSpacing: "0.08em" }}>
                  Customer Cloud Console
                </p>
              </div>

              {error && (
                <div style={{ display: "flex", alignItems: "center", gap: "8px", background: "#FEF2F2", border: "1px solid #FECACA", color: "#DC2626", padding: "12px", borderRadius: "8px", fontSize: "0.85rem", marginBottom: "20px" }}>
                  <ShieldAlert size={16} />
                  <span>{error}</span>
                </div>
              )}

              {info && (
                <div style={{ display: "flex", alignItems: "center", gap: "8px", background: "#FFFBEB", border: "1px solid #FDE68A", color: "#B45309", padding: "12px", borderRadius: "8px", fontSize: "0.85rem", marginBottom: "20px" }}>
                  <Info size={16} />
                  <span>{info}</span>
                </div>
              )}

              {isLoginView ? (
                /* LOGIN FORM */
                <form onSubmit={handleLogin} style={{ display: "flex", flexDirection: "column", gap: "18px" }}>
                  <h2 style={{ fontSize: "1.2rem", fontWeight: "700", textAlign: "center", color: "#0F172A" }}>Log In to Your Cloud Console</h2>
                  
                  <div>
                    <label style={{ display: "block", fontSize: "0.8rem", color: "#475569", marginBottom: "6px", fontWeight: "500" }}>Account Email</label>
                    <div style={{ position: "relative" }}>
                      <Mail size={16} style={{ position: "absolute", left: "14px", top: "14px", color: "#94A3B8" }} />
                      <input 
                        type="email" 
                        className="input-field" 
                        placeholder="you@company.com" 
                        style={{ paddingLeft: "42px" }}
                        value={email}
                        onChange={(e) => setEmail(e.target.value)}
                        required
                      />
                    </div>
                  </div>

                  <div>
                    <label style={{ display: "block", fontSize: "0.8rem", color: "#475569", marginBottom: "6px", fontWeight: "500" }}>Password</label>
                    <div style={{ position: "relative" }}>
                      <Lock size={16} style={{ position: "absolute", left: "14px", top: "14px", color: "#94A3B8" }} />
                      <input 
                        type="password" 
                        className="input-field" 
                        placeholder="••••••••" 
                        style={{ paddingLeft: "42px" }}
                        value={password}
                        onChange={(e) => setPassword(e.target.value)}
                        required
                      />
                    </div>
                  </div>

                  <button type="submit" className="btn btn-primary" style={{ width: "100%", padding: "12px", marginTop: "8px" }} disabled={loading}>
                    {loading ? "Authenticating Session..." : "Sign In to Console"}
                  </button>

                  <p style={{ fontSize: "0.82rem", color: "#64748B", textAlign: "center", marginTop: "10px" }}>
                    New to Neuravolt?{" "}
                    <button type="button" onClick={() => { setIsLoginView(false); setError(""); setInfo(""); }} style={{ background: "none", border: "none", color: "#FF7722", fontWeight: "600", cursor: "pointer" }}>
                      Create an account
                    </button>
                  </p>
                </form>
              ) : (
                /* SIGNUP FORM */
                <form onSubmit={handleSignup} style={{ display: "flex", flexDirection: "column", gap: "15px" }}>
                  <h2 style={{ fontSize: "1.2rem", fontWeight: "700", textAlign: "center", color: "#0F172A" }}>Create Your Cloud Account</h2>

                  <div>
                    <label style={{ display: "block", fontSize: "0.78rem", color: "#475569", marginBottom: "4px", fontWeight: "500" }}>Full Name</label>
                    <div style={{ position: "relative" }}>
                      <User size={14} style={{ position: "absolute", left: "14px", top: "12px", color: "#94A3B8" }} />
                      <input 
                        type="text" 
                        className="input-field" 
                        placeholder="Rahul Sharma" 
                        style={{ paddingLeft: "40px", paddingBlock: "8px", fontSize: "0.85rem" }}
                        value={name}
                        onChange={(e) => setName(e.target.value)}
                        required
                      />
                    </div>
                  </div>

                  <div>
                    <label style={{ display: "block", fontSize: "0.78rem", color: "#475569", marginBottom: "4px", fontWeight: "500" }}>Email address</label>
                    <div style={{ position: "relative" }}>
                      <Mail size={14} style={{ position: "absolute", left: "14px", top: "12px", color: "#94A3B8" }} />
                      <input 
                        type="email" 
                        className="input-field" 
                        placeholder="rahul@agency.in" 
                        style={{ paddingLeft: "40px", paddingBlock: "8px", fontSize: "0.85rem" }}
                        value={email}
                        onChange={(e) => setEmail(e.target.value)}
                        required
                      />
                    </div>
                  </div>

                  <div>
                    <label style={{ display: "block", fontSize: "0.78rem", color: "#475569", marginBottom: "4px", fontWeight: "500" }}>Company / Agency</label>
                    <div style={{ position: "relative" }}>
                      <Building size={14} style={{ position: "absolute", left: "14px", top: "12px", color: "#94A3B8" }} />
                      <input 
                        type="text" 
                        className="input-field" 
                        placeholder="Sharma Marketing Ltd." 
                        style={{ paddingLeft: "40px", paddingBlock: "8px", fontSize: "0.85rem" }}
                        value={company}
                        onChange={(e) => setCompany(e.target.value)}
                      />
                    </div>
                  </div>

                  <div>
                    <label style={{ display: "block", fontSize: "0.78rem", color: "#475569", marginBottom: "4px", fontWeight: "500" }}>Console Password</label>
                    <div style={{ position: "relative" }}>
                      <Lock size={14} style={{ position: "absolute", left: "14px", top: "12px", color: "#94A3B8" }} />
                      <input 
                        type="password" 
                        className="input-field" 
                        placeholder="••••••••" 
                        style={{ paddingLeft: "40px", paddingBlock: "8px", fontSize: "0.85rem" }}
                        value={password}
                        onChange={(e) => setPassword(e.target.value)}
                        required
                      />
                    </div>
                  </div>

                  <div>
                    <label style={{ display: "block", fontSize: "0.78rem", color: "#475569", marginBottom: "4px", fontWeight: "500" }}>Desired Infrastructure Plan</label>
                    <select 
                      className="input-field"
                      style={{ paddingBlock: "8px", fontSize: "0.85rem" }}
                      value={plan}
                      onChange={(e) => setPlan(e.target.value)}
                    >
                      <option value="BASIC">n8n Basic - ₹999/mo (0.5 CPU, 512MB RAM)</option>
                      <option value="PRO">n8n Pro Node - ₹1999/mo (1.0 CPU, 1024MB RAM)</option>
                      <option value="HEAVY">n8n Heavy Enterprise - ₹3999/mo (2.0 CPU, 2048MB RAM)</option>
                    </select>
                  </div>

                  <button type="submit" className="btn btn-primary" style={{ width: "100%", padding: "10px", marginTop: "5px" }} disabled={loading}>
                    {loading ? "Registering Account..." : "Create Account"}
                  </button>

                  <p style={{ fontSize: "0.82rem", color: "#64748B", textAlign: "center", marginTop: "8px" }}>
                    Already have an account?{" "}
                    <button type="button" onClick={() => { setIsLoginView(true); setError(""); setInfo(""); }} style={{ background: "none", border: "none", color: "#FF7722", fontWeight: "600", cursor: "pointer" }}>
                      Sign in
                    </button>
                  </p>
                </form>
              )}
            </div>
          </div>
        </body>
      </html>
    );
  }

  return (
    <html lang="en">
      <body>
        <div style={{ display: "flex", minHeight: "100vh" }}>
          {/* User Sidebar */}
          <aside className="glass-card" style={{ width: "260px", borderRadius: "0", borderLeft: "none", borderTop: "none", borderBottom: "none", borderRight: "1px solid #E2E8F0", background: "#FFFFFF", display: "flex", flexDirection: "column", padding: "30px 20px", position: "fixed", height: "100vh", zIndex: 100 }}>
            <div style={{ display: "flex", flexDirection: "column", alignItems: "flex-start", gap: "6px", marginBottom: "36px" }}>
              <img 
                src="https://neuravolt.cloud/assets/logos/neuravolt_cloud_logo.png" 
                alt="NeuraVolt Cloud" 
                style={{ height: "36px", maxWidth: "180px", objectFit: "contain" }} 
              />
              <span style={{ fontSize: "0.7rem", color: "#64748B", fontWeight: "600", textTransform: "uppercase", letterSpacing: "0.06em", paddingLeft: "2px" }}>
                Client Operator Portal
              </span>
            </div>

            <nav style={{ display: "flex", flexDirection: "column", gap: "8px", flex: 1 }}>
              <button 
                onClick={() => navigateTo("dashboard", "/")}
                className="btn" 
                style={{ justifyContent: "flex-start", width: "100%", background: activeTab === "dashboard" ? "#FFF7ED" : "transparent", color: activeTab === "dashboard" ? "#FF7722" : "#475569", fontWeight: activeTab === "dashboard" ? "600" : "500" }}
              >
                <LayoutDashboard size={18} />
                <span>My Dashboard</span>
              </button>

              <button 
                onClick={() => navigateTo("billing", "/billing")}
                className="btn" 
                style={{ justifyContent: "flex-start", width: "100%", background: activeTab === "billing" ? "#FFF7ED" : "transparent", color: activeTab === "billing" ? "#FF7722" : "#475569", fontWeight: activeTab === "billing" ? "600" : "500" }}
              >
                <IndianRupee size={18} />
                <span>My Invoices</span>
              </button>

              <button 
                onClick={() => navigateTo("settings", "/settings")}
                className="btn" 
                style={{ justifyContent: "flex-start", width: "100%", background: activeTab === "settings" ? "#FFF7ED" : "transparent", color: activeTab === "settings" ? "#FF7722" : "#475569", fontWeight: activeTab === "settings" ? "600" : "500" }}
              >
                <Settings size={18} />
                <span>Settings</span>
              </button>
            </nav>

            <div style={{ borderTop: "1px solid #F1F5F9", paddingTop: "20px" }}>
              <div style={{ display: "flex", alignItems: "center", gap: "10px", marginBottom: "15px", padding: "0 8px" }}>
                <div style={{ width: "32px", height: "32px", borderRadius: "50%", background: "#FFF7ED", border: "1px solid #FFEDD5", color: "#FF7722", display: "flex", alignItems: "center", justifyContent: "center", fontSize: "0.85rem", fontWeight: "700" }}>
                  U
                </div>
                <div>
                  <p style={{ fontSize: "0.85rem", fontWeight: "600", color: "#0F172A", whiteSpace: "nowrap", overflow: "hidden", textOverflow: "ellipsis", maxWidth: "150px" }}>
                    {typeof window !== "undefined" ? localStorage.getItem("nv_user_name") : "Client"}
                  </p>
                  <p style={{ fontSize: "0.7rem", color: "#64748B" }}>Client Operator</p>
                </div>
              </div>
              <button onClick={handleLogout} className="btn btn-danger" style={{ width: "100%" }}>
                <LogOut size={16} />
                <span>Sign Out</span>
              </button>
            </div>
          </aside>

          <main style={{ marginLeft: "260px", flex: 1, padding: "40px", minHeight: "100vh", background: "#F8FAFC" }}>
            {children}
          </main>
        </div>
      </body>
    </html>
  );
}
