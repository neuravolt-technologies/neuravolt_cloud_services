"use client";

import React, { useState, useEffect } from "react";
import { User, ShieldCheck, Mail, Building, Sparkles } from "lucide-react";

export default function UserSettings() {
  const [name, setName] = useState("Rahul Sharma");
  const [email, setEmail] = useState("rahul@agency.in");
  const [company, setCompany] = useState("Sharma Marketing Agency");
  const [success, setSuccess] = useState(false);

  useEffect(() => {
    if (typeof window !== "undefined") {
      setName(localStorage.getItem("nv_user_name") || "Rahul Sharma");
      setEmail(localStorage.getItem("nv_user_email") || "rahul@agency.in");
    }
  }, []);

  const handleSave = (e: React.FormEvent) => {
    e.preventDefault();
    if (typeof window !== "undefined") {
      localStorage.setItem("nv_user_name", name);
    }
    setSuccess(true);
    setTimeout(() => setSuccess(false), 3000);
  };

  const triggerPlanUpgrade = () => {
    alert("An upgrade proposal alert has been dispatched to the Neuravolt administration. They will contact you shortly!");
  };

  return (
    <div style={{ maxWidth: "600px" }}>
      {/* Header */}
      <div style={{ marginBottom: "30px" }}>
        <h1 style={{ fontSize: "1.75rem", fontWeight: "700", color: "#0F172A" }}>Account Profile Settings</h1>
        <p style={{ color: "#64748B", fontSize: "0.9rem" }}>Update contact details and request resource upgrades</p>
      </div>

      {success && (
        <div style={{ display: "flex", alignItems: "center", gap: "8px", background: "#ECFDF5", border: "1px solid #A7F3D0", color: "#059669", padding: "12px", borderRadius: "8px", fontSize: "0.85rem", marginBottom: "20px" }}>
          <ShieldCheck size={16} />
          <span>Profile parameters updated successfully.</span>
        </div>
      )}

      {/* Profile Form */}
      <div className="glass-card" style={{ padding: "30px", marginBottom: "30px" }}>
        <form onSubmit={handleSave} style={{ display: "flex", flexDirection: "column", gap: "20px" }}>
          <div>
            <label style={{ display: "block", fontSize: "0.8rem", color: "#475569", marginBottom: "6px", fontWeight: "500" }}>Full Name</label>
            <div style={{ position: "relative" }}>
              <User size={16} style={{ position: "absolute", left: "14px", top: "14px", color: "#94A3B8" }} />
              <input 
                type="text" 
                className="input-field" 
                style={{ paddingLeft: "42px" }}
                value={name}
                onChange={(e) => setName(e.target.value)}
                required
              />
            </div>
          </div>

          <div>
            <label style={{ display: "block", fontSize: "0.8rem", color: "#475569", marginBottom: "6px", fontWeight: "500" }}>Email (Read-only)</label>
            <div style={{ position: "relative" }}>
              <Mail size={16} style={{ position: "absolute", left: "14px", top: "14px", color: "#94A3B8" }} />
              <input 
                type="email" 
                className="input-field" 
                style={{ paddingLeft: "42px", background: "#F1F5F9", color: "#64748B" }}
                value={email}
                readOnly
              />
            </div>
          </div>

          <div>
            <label style={{ display: "block", fontSize: "0.8rem", color: "#475569", marginBottom: "6px", fontWeight: "500" }}>Company Name</label>
            <div style={{ position: "relative" }}>
              <Building size={16} style={{ position: "absolute", left: "14px", top: "14px", color: "#94A3B8" }} />
              <input 
                type="text" 
                className="input-field" 
                style={{ paddingLeft: "42px" }}
                value={company}
                onChange={(e) => setCompany(e.target.value)}
              />
            </div>
          </div>

          <button type="submit" className="btn btn-primary" style={{ alignSelf: "flex-start", padding: "10px 24px" }}>
            Save Changes
          </button>
        </form>
      </div>

      {/* Scale proposal */}
      <div className="glass-card" style={{ padding: "30px", border: "1px solid #FFEDD5", background: "#FFF7ED" }}>
        <h3 style={{ fontSize: "1.1rem", fontWeight: "700", color: "#0F172A", marginBottom: "8px", display: "flex", alignItems: "center", gap: "8px" }}>
          <Sparkles size={18} style={{ color: "#FF7722" }} />
          <span>Scale My Compute Space</span>
        </h3>
        <p style={{ fontSize: "0.85rem", color: "#64748B", lineHeight: "1.5", marginBottom: "20px" }}>
          Need more processing capacity? Request a scaling up directly. Our systems can upgrade basic sandboxes to PRO or HEAVY tiers instantly upon approval with zero container downtimes.
        </p>
        <button onClick={triggerPlanUpgrade} className="btn btn-primary" style={{ padding: "10px 20px" }}>
          Submit Upgrade Request
        </button>
      </div>
    </div>
  );
}
