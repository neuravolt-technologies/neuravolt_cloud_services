# NeuraVolt Cloud Services

The unified cloud infrastructure repository for **NeuraVolt Technologies**. This repository powers the primary public marketing site, client management portals, developer backend APIs, Reach cold email outreach platform, and monitoring stack.

---

## 🚀 Quick Navigation & Subdomains

- **Official Website**: [https://neuravolt.cloud](https://neuravolt.cloud) (`landing/` directory)
- **Reach Outreach Platform**: [https://reach.neuravolt.cloud](https://reach.neuravolt.cloud) (`voltmail/` directory)
- **Customer Cloud Portal**: [https://app.neuravolt.cloud](https://app.neuravolt.cloud) (`app/` directory)
- **Cloud Backend API**: [https://api.neuravolt.cloud](https://api.neuravolt.cloud) (`backend/` directory)
- **Monitoring & Metrics**: [https://monitor.neuravolt.cloud](https://monitor.neuravolt.cloud) (`monitoring/` directory)

---

## 📖 Complete Technical Documentation

For the comprehensive technical architecture guide, service mapping, port allocations, CI/CD specifications, and operational playbooks, see:

👉 **[SYSTEM_ARCHITECTURE.md](SYSTEM_ARCHITECTURE.md)**

---

## 🛠️ Automated CI/CD Deployment

Deployments to production VPS (`187.127.148.197`) are fully automated via GitHub Actions:
- **Production**: Push to `main` branch
- **Staging**: Push to `staging` branch
- **Workflow**: `.github/workflows/deploy.yml`
- **Execution Script**: `deploy.sh`
