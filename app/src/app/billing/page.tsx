"use client";

import React, { useState, useEffect } from "react";
import { CreditCard, IndianRupee, RefreshCw, FileText, CheckCircle2 } from "lucide-react";
import ApiClient from "../../lib/api";

interface Invoice {
  id: string;
  amount: string;
  currency: string;
  status: "PENDING" | "PAID" | "OVERDUE" | "CANCELLED";
  dueDate: string;
  paidAt: string | null;
  items: any;
}

export default function UserBilling() {
  const [invoices, setInvoices] = useState<Invoice[]>([]);
  const [loading, setLoading] = useState(true);
  const [actionId, setActionId] = useState<string | null>(null);

  const loadInvoices = async () => {
    try {
      const data = await ApiClient.get<Invoice[]>("/billing/invoices");
      setInvoices(data);
    } catch (err) {
      console.warn("API invoices offline, fallback mocks.");
      setInvoices([
        {
          id: "inv_ae89",
          amount: "999.00",
          currency: "INR",
          status: "PAID",
          dueDate: new Date(Date.now() + 2 * 24 * 60 * 60 * 1000).toDateString(),
          paidAt: new Date().toDateString(),
          items: [{ name: "BASIC Subscription Fee", amount: 999 }]
        },
        {
          id: "inv_cd42",
          amount: "999.00",
          currency: "INR",
          status: "PENDING",
          dueDate: new Date(Date.now() - 4 * 24 * 60 * 60 * 1000).toDateString(),
          paidAt: null,
          items: [{ name: "BASIC Subscription Fee", amount: 999 }]
        }
      ]);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    loadInvoices();
  }, []);

  const handlePay = async (id: string) => {
    setActionId(id);
    try {
      await ApiClient.post(`/billing/invoices/${id}/pay`);
      await loadInvoices();
    } catch (err: any) {
      alert(err.message || "Failed to make payment");
    } finally {
      setActionId(null);
    }
  };

  return (
    <div>
      {/* Header */}
      <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center", marginBottom: "30px" }}>
        <div>
          <h1 style={{ fontSize: "1.75rem", fontWeight: "700", color: "#0F172A" }}>My Invoices & Accounts</h1>
          <p style={{ color: "#64748B", fontSize: "0.9rem" }}>Review billing cycles, pay outstanding dues, and trace receipts</p>
        </div>
      </div>

      {/* Grid of lists */}
      <div className="glass-card" style={{ padding: "10px", overflow: "hidden" }}>
        {loading ? (
          <div style={{ display: "flex", justifyContent: "center", alignItems: "center", padding: "60px", color: "#64748B" }}>
            Loading ledger...
          </div>
        ) : invoices.length === 0 ? (
          <div style={{ padding: "50px", textAlign: "center", color: "#64748B" }}>
            No invoice statements have been generated for your account yet.
          </div>
        ) : (
          <div className="table-container">
            <table className="custom-table">
              <thead>
                <tr>
                  <th>Invoice reference</th>
                  <th>Core plan detail</th>
                  <th>Amount Dues</th>
                  <th>Status</th>
                  <th>Due Date</th>
                  <th>Payment checkout</th>
                </tr>
              </thead>
              <tbody>
                {invoices.map((inv) => (
                  <tr key={inv.id}>
                    <td style={{ fontFamily: "monospace", fontSize: "0.85rem", fontWeight: "600", color: "#0F172A" }}>
                      #{inv.id}
                    </td>
                    <td>
                      <div style={{ display: "flex", gap: "8px", alignItems: "center", color: "#1E293B" }}>
                        <FileText size={16} style={{ color: "#94A3B8" }} />
                        <span>Monthly Subscription Bill</span>
                      </div>
                    </td>
                    <td>
                      <div style={{ fontWeight: "700", color: "#0F172A" }}>₹{parseFloat(inv.amount).toFixed(2)}</div>
                    </td>
                    <td>
                      <span className={`badge badge-${inv.status.toLowerCase()}`}>{inv.status}</span>
                    </td>
                    <td>
                      <span style={{ fontSize: "0.85rem", color: inv.status === "PENDING" ? "#DC2626" : "#475569", fontWeight: inv.status === "PENDING" ? "600" : "400" }}>
                        {new Date(inv.dueDate).toDateString()}
                      </span>
                    </td>
                    <td>
                      {inv.status === "PENDING" ? (
                        <button 
                          onClick={() => handlePay(inv.id)}
                          disabled={actionId !== null}
                          className="btn btn-primary" 
                          style={{ fontSize: "0.75rem", padding: "6px 14px" }}
                        >
                          <CreditCard size={12} />
                          <span>Pay Dues (Mock UPI)</span>
                        </button>
                      ) : (
                        <div style={{ color: "#059669", fontSize: "0.8rem", display: "flex", gap: "4px", alignItems: "center", fontWeight: "600" }}>
                          <CheckCircle2 size={14} />
                          <span>Paid Receipt ({new Date(inv.paidAt || "").toLocaleDateString()})</span>
                        </div>
                      )}
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        )}
      </div>
    </div>
  );
}
