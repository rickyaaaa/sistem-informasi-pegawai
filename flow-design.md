# 📊 Flowchart Sistem Informasi Pegawai Honorer

Dokumen ini berisi **flowchart database** dan **flowchart alur kerja aplikasi** hasil analisis mendalam terhadap seluruh migration, model, controller, route, dan data JSON ekspor database.

---

## 1. Flowchart Database (Struktur & Relasi Tabel)

### 1A. Struktur Tabel Utama

```mermaid
flowchart TB
    subgraph SATKERS["📋 Tabel: satkers"]
        direction TB
        S_ID["🔑 id (PK, bigint)"]
        S_NAMA["nama_satker (varchar, UNIQUE)"]
        S_CA["created_at (timestamp)"]
        S_UA["updated_at (timestamp)"]
    end

    subgraph USERS["👥 Tabel: users"]
        direction TB
        U_ID["🔑 id (PK, bigint)"]
        U_NAME["name (varchar)"]
        U_USER["username (varchar, UNIQUE)"]
        U_PASS["password (varchar)"]
        U_TOKEN["remember_token (varchar)"]
        U_ROLE["role (enum: super_admin | admin_satker)"]
        U_SATKER["🔗 satker_id (FK → satkers.id, nullable)"]
        U_STATUS["status (enum: active | inactive)"]
        U_CA["created_at (timestamp)"]
        U_UA["updated_at (timestamp)"]
    end

    subgraph PEGAWAIS["📄 Tabel: pegawais"]
        direction TB
        P_ID["🔑 id (PK, bigint)"]
        P_NAMA["nama (varchar)"]
        P_NIK["nik (varchar, UNIQUE)"]
        P_JK["jenis_kelamin (varchar, nullable)"]
        P_FOTO["foto (varchar, nullable - path file)"]
        P_PEND["pendidikan (varchar)"]
        P_SATKER["🔗 satker_id (FK → satkers.id)"]
        P_STATUS["status (enum: aktif | non_aktif)"]
        P_KTP["file_ktp (varchar, nullable - path file)"]
        P_KK["file_kk (varchar, nullable - path file)"]
        P_CA["created_at (timestamp)"]
        P_UA["updated_at (timestamp)"]
        P_DA["deleted_at (timestamp - SoftDeletes)"]
    end

    subgraph REQUESTS["📝 Tabel: pegawai_requests"]
        direction TB
        R_ID["🔑 id (PK, bigint)"]
        R_PEG["🔗 pegawai_id (FK → pegawais.id, nullable)"]
        R_SATKER["🔗 satker_id (FK → satkers.id)"]
        R_REQBY["🔗 requested_by (FK → users.id)"]
        R_ACTION["action_type (enum: create | update | delete)"]
        R_DATA["data_payload (JSON, nullable)"]
        R_STATUS["status (enum: pending | approved | rejected)"]
        R_APPBY["🔗 approved_by (FK → users.id, nullable)"]
        R_APPAT["approved_at (timestamp, nullable)"]
        R_CA["created_at (timestamp)"]
        R_UA["updated_at (timestamp)"]
    end

    subgraph SESSIONS["🔐 Tabel: sessions"]
        direction TB
        SS_ID["🔑 id (PK, varchar)"]
        SS_USER["🔗 user_id (FK → users.id, nullable)"]
        SS_IP["ip_address (varchar, nullable)"]
        SS_UA["user_agent (text, nullable)"]
        SS_PL["payload (longtext)"]
        SS_LA["last_activity (int)"]
    end

    subgraph PRT["🔑 Tabel: password_reset_tokens"]
        direction TB
        PRT_EMAIL["🔑 email (PK, varchar)"]
        PRT_TOKEN["token (varchar)"]
        PRT_CA["created_at (timestamp, nullable)"]
    end
```

### 1B. Relasi Antar Tabel (Foreign Key)

```mermaid
flowchart LR
    SATKERS_T[("🏢 satkers")]

    USERS_T[("👥 users")]
    PEGAWAIS_T[("📄 pegawais")]
    REQUESTS_T[("📝 pegawai_requests")]
    SESSIONS_T[("🔐 sessions")]

    SATKERS_T -- "1 : N\nsatker_id\n(nullOnDelete)" --> USERS_T
    SATKERS_T -- "1 : N\nsatker_id\n(constrained)" --> PEGAWAIS_T
    SATKERS_T -- "1 : N\nsatker_id\n(constrained)" --> REQUESTS_T

    USERS_T -- "1 : N\nrequested_by\n(constrained)" --> REQUESTS_T
    USERS_T -- "1 : N\napproved_by\n(nullOnDelete)" --> REQUESTS_T
    USERS_T -- "1 : N\nuser_id" --> SESSIONS_T

    PEGAWAIS_T -- "1 : N\npegawai_id\n(nullOnDelete)" --> REQUESTS_T
```

### Penjelasan Relasi Database

| Relasi | Tipe | Foreign Key | Constraint |
|--------|------|-------------|------------|
| `satkers` → `users` | One-to-Many | `users.satker_id` | `nullOnDelete` (jika satker dihapus, satker_id jadi NULL) |
| `satkers` → `pegawais` | One-to-Many | `pegawais.satker_id` | `constrained` (tidak bisa hapus satker jika masih ada pegawai) |
| `satkers` → `pegawai_requests` | One-to-Many | `pegawai_requests.satker_id` | `constrained` |
| `users` → `pegawai_requests` (pembuat) | One-to-Many | `pegawai_requests.requested_by` | `constrained` |
| `users` → `pegawai_requests` (approver) | One-to-Many | `pegawai_requests.approved_by` | `nullOnDelete` |
| `pegawais` → `pegawai_requests` | One-to-Many | `pegawai_requests.pegawai_id` | `nullable`, `nullOnDelete` |

> **PENTING:** Tabel `pegawais` menggunakan **SoftDeletes** — data tidak benar-benar dihapus dari database, hanya ditandai dengan `deleted_at`. Ini memastikan riwayat `pegawai_requests` tetap bisa di-trace.

---

## 2. Flowchart Alur Kerja Aplikasi (System Flow)

### 2A. Alur Autentikasi & Otorisasi

```mermaid
flowchart TD
    START(["🌐 User Akses Aplikasi"]) --> GUEST{"Sudah Login?"}

    GUEST -- Belum --> LOGIN["Halaman Login\nPOST /login"]
    LOGIN --> VALIDATE{"Username &\nPassword Valid?"}
    VALIDATE -- Tidak --> ERRLOGIN["❌ Tampilkan Error"]
    ERRLOGIN --> LOGIN
    VALIDATE -- Ya --> CHECKSTATUS{"Status Akun\nActive?"}
    CHECKSTATUS -- Tidak --> ERRINACTIVE["❌ Akun Nonaktif"]
    ERRINACTIVE --> LOGIN
    CHECKSTATUS -- Ya --> CHECKROLE{"Cek Role User"}

    GUEST -- Sudah --> CHECKROLE

    CHECKROLE -- super_admin --> SA_DASH["🏠 Dashboard Admin Polda\nFull Access"]
    CHECKROLE -- admin_satker --> OP_DASH["🏠 Dashboard Operator\nScoped to Satker"]
```

### 2B. Alur Utama Admin Polda

```mermaid
flowchart TD
    SA["👑 Admin Polda\nDashboard"] --> SA_MENU{"Pilih Menu"}

    SA_MENU --> M_SATKER["📋 Kelola Satker"]
    SA_MENU --> M_USER["👥 Kelola User"]
    SA_MENU --> M_PEGAWAI["📄 Kelola Pegawai"]
    SA_MENU --> M_APPROVAL["✅ Approval Center"]
    SA_MENU --> M_PROFILE["👤 Edit Profile"]

    %% ── Satker CRUD ──
    M_SATKER --> S_LIST["Lihat Daftar Satker\nGET /satker"]
    S_LIST --> S_CREATE["Tambah Satker"]
    S_LIST --> S_EDIT["Edit Satker"]
    S_LIST --> S_DELETE["Hapus Satker"]
    S_CREATE --> S_STORE["POST /satker\n→ INSERT DB satkers"]
    S_EDIT --> S_UPDATE["PUT /satker/id\n→ UPDATE DB satkers"]
    S_DELETE --> S_DESTROY["DELETE /satker/id\n→ DELETE DB satkers"]

    %% ── User Management ──
    M_USER --> U_LIST["Lihat Daftar User\nGET /users"]
    U_LIST --> U_CREATE["Tambah User Baru"]
    U_LIST --> U_EDIT["Edit User"]
    U_LIST --> U_DELETE["Hapus User"]
    U_LIST --> U_TOGGLE["Toggle Status\nActive/Inactive"]
    U_CREATE --> U_STORE["POST /users\n→ INSERT DB users"]
    U_EDIT --> U_UPDATE["PUT /users/id\n→ UPDATE DB users"]
    U_DELETE --> U_DESTROY["DELETE /users/id\n→ DELETE DB users"]
    U_TOGGLE --> U_PATCH["PATCH /users/id/toggle-status\n→ UPDATE DB users"]

    %% ── Pegawai Direct CRUD ──
    M_PEGAWAI --> P_LIST["Lihat Daftar Pegawai\nGET /pegawai\nFilter: satker, nama/NIK"]
    P_LIST --> P_CREATE["Tambah Pegawai"]
    P_LIST --> P_SHOW["Detail Pegawai"]
    P_LIST --> P_EDIT["Edit Pegawai"]
    P_LIST --> P_DELETE["Hapus Pegawai"]
    P_LIST --> P_EXPORT["📥 Export Excel"]
    P_CREATE --> P_STORE_SA["POST /pegawai\n→ INSERT langsung ke DB pegawais"]
    P_EDIT --> P_UPDATE_SA["PUT /pegawai/id\n→ UPDATE langsung ke DB pegawais"]
    P_DELETE --> P_DESTROY_SA["DELETE /pegawai/id\n→ Soft Delete di DB pegawais"]

    %% ── Approval Workflow ──
    M_APPROVAL --> A_LIST["Lihat Semua Request\nGET /approvals\nFilter: status"]
    A_LIST --> A_REVIEW{"Review Request"}
    A_REVIEW -- Setuju --> A_APPROVE["POST /approvals/id/approve"]
    A_REVIEW -- Tolak --> A_REJECT["POST /approvals/id/reject"]

    A_APPROVE --> A_APPLY{"Terapkan Action"}
    A_APPLY -- create --> A_INSERT["INSERT ke DB pegawais"]
    A_APPLY -- update --> A_UPDATE_PEG["UPDATE di DB pegawais"]
    A_APPLY -- delete --> A_SOFT_DEL["Soft Delete di DB pegawais"]

    A_REJECT --> A_CLEAN["Hapus file uploaded\nUpdate status → rejected"]

    style SA fill:#1a1a2e,color:#fff,stroke:#e94560
    style M_APPROVAL fill:#0f3460,color:#fff
    style A_APPROVE fill:#16a085,color:#fff
    style A_REJECT fill:#e74c3c,color:#fff
```

### 2C. Alur Operator (Admin Satker) — Dengan Approval Workflow

```mermaid
flowchart TD
    OP["🔧 Operator Dashboard\nScoped ke Satker sendiri"] --> OP_MENU{"Pilih Menu"}

    OP_MENU --> OP_PEG["📄 Kelola Pegawai\nHanya milik Satker sendiri"]
    OP_MENU --> OP_PROF["👤 Edit Profile"]

    OP_PEG --> OP_LIST["Lihat Daftar Pegawai\nAuto-filter by satker_id"]

    OP_LIST --> OP_CREATE["Tambah Pegawai Baru"]
    OP_LIST --> OP_SHOW["Detail Pegawai"]
    OP_LIST --> OP_EDIT["Edit Pegawai"]
    OP_LIST --> OP_DELETE["Hapus Pegawai"]
    OP_LIST --> OP_EXPORT["📥 Export Excel"]

    OP_CREATE --> REQ_C["Buat PegawaiRequest\naction_type: create\nstatus: pending"]
    OP_EDIT --> REQ_U["Buat PegawaiRequest\naction_type: update\nstatus: pending"]
    OP_DELETE --> REQ_D["Buat PegawaiRequest\naction_type: delete\nstatus: pending"]

    REQ_C --> WAIT["⏳ Menunggu Persetujuan\nAdmin Polda"]
    REQ_U --> WAIT
    REQ_D --> WAIT

    WAIT --> SA_REVIEW{"Admin Polda Review"}
    SA_REVIEW -- Approved --> APPLIED["✅ Perubahan Diterapkan\nke DB pegawais"]
    SA_REVIEW -- Rejected --> DITOLAK["❌ Permintaan Ditolak\nFile di-cleanup"]

    style OP fill:#2c3e50,color:#fff,stroke:#3498db
    style WAIT fill:#f39c12,color:#000
    style APPLIED fill:#27ae60,color:#fff
    style DITOLAK fill:#c0392b,color:#fff
```

### 2D. Lifecycle Approval Request (pegawai_requests)

```mermaid
flowchart LR
    START(["Operator Submit"]) --> PENDING["📋 Status: PENDING\ndata_payload = snapshot data\naction_type = create/update/delete\napproved_by = NULL"]

    PENDING --> REVIEW{"Admin Polda\nReview"}

    REVIEW -- Approve --> APPROVED["✅ Status: APPROVED\napproved_by = Admin Polda ID\napproved_at = timestamp"]
    REVIEW -- Reject --> REJECTED["❌ Status: REJECTED\napproved_by = Admin Polda ID\napproved_at = timestamp"]

    APPROVED --> APPLY_ACTION{"Terapkan Action"}
    APPLY_ACTION -- create --> DO_CREATE["INSERT pegawai baru\nke tabel pegawais"]
    APPLY_ACTION -- update --> DO_UPDATE["UPDATE data pegawai\ndi tabel pegawais"]
    APPLY_ACTION -- delete --> DO_DELETE["Soft Delete pegawai\ndi tabel pegawais"]

    REJECTED --> CLEANUP["🗑️ Hapus file KTP/KK\nyang sudah diupload"]

    style PENDING fill:#f39c12,color:#000
    style APPROVED fill:#27ae60,color:#fff
    style REJECTED fill:#c0392b,color:#fff
    style DO_CREATE fill:#2ecc71,color:#fff
    style DO_UPDATE fill:#3498db,color:#fff
    style DO_DELETE fill:#e67e22,color:#fff
```

---

## 3. Ringkasan Arsitektur

### Role-Based Access Control (RBAC)

| Fitur | Admin Polda | Operator (Admin Satker) |
|-------|:-----------:|:----------------------:|
| Dashboard | ✅ Semua data | ✅ Data satker sendiri |
| Kelola Satker | ✅ CRUD langsung | ❌ |
| Kelola User | ✅ CRUD + toggle status | ❌ |
| Kelola Pegawai | ✅ CRUD langsung | ⏳ Via approval request |
| Approval Center | ✅ Review & approve/reject | ❌ |
| Export Excel | ✅ | ✅ |
| Edit Profile | ✅ | ✅ |

### Data Teraktual dari Database

Berdasarkan JSON ekspor:

| Tabel | Jumlah Record | Keterangan |
|-------|:---:|-------------|
| `satkers` | 3 | Bagian Umum, Bagian Keuangan, Bagian Kepegawaian |
| `users` | 2 | 1 Admin Polda, 1 Operator (Satker Umum) |
| `pegawais` | 20 | 8 di Satker 1, 6 di Satker 2, 6 di Satker 3 |
| `pegawai_requests` | 0 | Belum ada request yang diajukan |
| `sessions` | 0 | Tidak ada session aktif |

> **CATATAN:** Semua 14 migration sudah berhasil dijalankan dalam 1 batch, menandakan database dalam kondisi konsisten dan up-to-date.
