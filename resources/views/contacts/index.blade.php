<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Kontak SMK</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        :root {
            --primary: #4f46e5;
            --primary-hover: #4338ca;
            --danger: #dc2626;
            --success: #16a34a;
            --border: #e5e7eb;
            --bg: #f9fafb;
            --text: #111827;
            --text-muted: #6b7280;
        }

        * { box-sizing: border-box; }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif;
            background: var(--bg);
            color: var(--text);
            margin: 0;
            padding: 40px 20px;
        }

        .wrap {
            max-width: 560px;
            margin: 0 auto;
        }

        h1 {
            font-size: 22px;
            margin: 0 0 4px;
        }

        .subtitle {
            color: var(--text-muted);
            font-size: 14px;
            margin: 0 0 24px;
        }

        .card {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 24px;
        }

        .card h2 {
            font-size: 15px;
            margin: 0 0 16px;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }

        label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 6px;
            color: var(--text);
        }

        .field { margin-bottom: 14px; }

        input[type="text"] {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid var(--border);
            border-radius: 8px;
            font-size: 14px;
            outline: none;
            transition: border-color 0.15s;
        }

        input[type="text"]:focus {
            border-color: var(--primary);
        }

        input.invalid {
            border-color: var(--danger);
        }

        .error {
            color: var(--danger);
            font-size: 12.5px;
            margin: 5px 0 0;
            min-height: 16px;
        }

        .actions {
            display: flex;
            gap: 8px;
            margin-top: 4px;
        }

        button {
            font-size: 14px;
            font-weight: 600;
            padding: 10px 18px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            transition: background 0.15s, opacity 0.15s;
        }

        #submitBtn {
            background: var(--primary);
            color: #fff;
        }
        #submitBtn:hover { background: var(--primary-hover); }
        #submitBtn:disabled { opacity: 0.6; cursor: default; }

        #cancelBtn {
            background: #fff;
            border: 1px solid var(--border);
            color: var(--text);
        }
        #cancelBtn:hover { background: #f3f4f6; }

        #toast {
            font-size: 13.5px;
            font-weight: 600;
            padding: 10px 14px;
            border-radius: 8px;
            margin: 0 0 16px;
            display: none;
        }
        #toast.show { display: block; }
        #toast.ok { background: #dcfce7; color: #15803d; }
        #toast.err { background: #fee2e2; color: #b91c1c; }

        #contactList { list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 8px; }

        #contactList li {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 12px 14px;
        }

        .contact-info p { margin: 0; }
        .contact-name { font-weight: 600; font-size: 14.5px; }
        .contact-meta { font-size: 13px; color: var(--text-muted); margin-top: 2px; }

        .icon-btn {
            background: transparent;
            border: 1px solid var(--border);
            padding: 6px 10px;
            font-size: 13px;
            font-weight: 500;
            margin-left: 6px;
        }
        .icon-btn:hover { background: #f3f4f6; }
        .icon-btn.delete { color: var(--danger); border-color: #fecaca; }
        .icon-btn.delete:hover { background: #fee2e2; }

        .empty-state {
            text-align: center;
            color: var(--text-muted);
            font-size: 14px;
            padding: 30px 0;
        }
    </style>
</head>
<body>
    <div class="wrap">
        <h1>Daftar Kontak</h1>
        <p class="subtitle">Tambah, ubah, dan hapus data tanpa reload halaman.</p>

        <div class="card">
            <h2 id="formTitle">Tambah Kontak</h2>
            <form id="contactForm">
                <input type="hidden" id="contactId">

                <div class="field">
                    <label for="name">Nama</label>
                    <input type="text" id="name" placeholder="Nama lengkap">
                    <p class="error" id="err-name"></p>
                </div>

                <div class="field">
                    <label for="email">Email</label>
                    <input type="text" id="email" placeholder="nama@email.com">
                    <p class="error" id="err-email"></p>
                </div>

                <div class="field">
                    <label for="phone">Telepon</label>
                    <input type="text" id="phone" placeholder="0812xxxxxxx">
                    <p class="error" id="err-phone"></p>
                </div>

                <div class="actions">
                    <button type="submit" id="submitBtn">Simpan</button>
                    <button type="button" id="cancelBtn" style="display:none;">Batal</button>
                </div>
            </form>
        </div>

        <p id="toast"></p>

        <ul id="contactList"></ul>
        <p class="empty-state" id="emptyState" style="display:none;">Belum ada data kontak.</p>
    </div>

    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        let contactsCache = [];

        async function loadContacts() {
            const res = await fetch('/contacts', {
                headers: { 'Accept': 'application/json' }
            });
            const data = await res.json();
            contactsCache = data.contacts;
            renderList();
        }

        function renderList() {
            const ul = document.getElementById('contactList');
            const empty = document.getElementById('emptyState');
            ul.innerHTML = '';

            if (contactsCache.length === 0) {
                empty.style.display = 'block';
                return;
            }
            empty.style.display = 'none';

            contactsCache.forEach(c => {
                const li = document.createElement('li');
                li.innerHTML = `
                    <div class="contact-info">
                        <p class="contact-name">${escapeHtml(c.name)}</p>
                        <p class="contact-meta">${escapeHtml(c.email)} · ${escapeHtml(c.phone)}</p>
                    </div>
                    <div>
                        <button type="button" class="icon-btn" onclick="startEdit(${c.id})">Edit</button>
                        <button type="button" class="icon-btn delete" onclick="doDelete(${c.id})">Hapus</button>
                    </div>
                `;
                ul.appendChild(li);
            });
        }

        // mencegah data user ditampilkan sebagai HTML mentah (keamanan dasar)
        function escapeHtml(str) {
            const div = document.createElement('div');
            div.textContent = str ?? '';
            return div.innerHTML;
        }

        function showErrors(errors) {
            ['name', 'email', 'phone'].forEach(field => {
                document.getElementById('err-' + field).textContent = '';
                document.getElementById(field).classList.remove('invalid');
            });
            if (!errors) return;
            Object.keys(errors).forEach(field => {
                const el = document.getElementById('err-' + field);
                const input = document.getElementById(field);
                if (el) el.textContent = errors[field][0];
                if (input) input.classList.add('invalid');
            });
        }

        function showToast(msg, isError) {
            const toast = document.getElementById('toast');
            toast.textContent = msg;
            toast.className = 'show ' + (isError ? 'err' : 'ok');
            setTimeout(() => {
                toast.classList.remove('show');
            }, 4000);
        }

        document.getElementById('contactForm').addEventListener('submit', async function (e) {
            e.preventDefault();

            const id = document.getElementById('contactId').value;
            const payload = {
                name: document.getElementById('name').value,
                email: document.getElementById('email').value,
                phone: document.getElementById('phone').value,
            };

            const url = id ? '/contacts/' + id : '/contacts';
            const method = id ? 'PUT' : 'POST';

            const submitBtn = document.getElementById('submitBtn');
            submitBtn.disabled = true;
            const originalLabel = submitBtn.textContent;
            submitBtn.textContent = 'Menyimpan...';

            try {
                const res = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify(payload)
                });

                const result = await res.json();

                if (!res.ok) {
                    if (res.status === 422) {
                        showErrors(result.errors);
                        showToast('Periksa kembali isian form.', true);
                    } else {
                        showToast(result.message || 'Terjadi kesalahan pada server.', true);
                    }
                    return;
                }

                showErrors(null);
                showToast(result.message, false);
                resetForm();
                loadContacts();

            } catch (err) {
                showToast('Tidak bisa terhubung ke server.', true);
            } finally {
                submitBtn.disabled = false;
                submitBtn.textContent = originalLabel;
            }
        });

        function resetForm() {
            document.getElementById('contactForm').reset();
            document.getElementById('contactId').value = '';
            document.getElementById('submitBtn').textContent = 'Simpan';
            document.getElementById('formTitle').textContent = 'Tambah Kontak';
            document.getElementById('cancelBtn').style.display = 'none';
            showErrors(null);
        }

        function startEdit(id) {
            const c = contactsCache.find(x => x.id === id);
            document.getElementById('contactId').value = c.id;
            document.getElementById('name').value = c.name;
            document.getElementById('email').value = c.email;
            document.getElementById('phone').value = c.phone;
            document.getElementById('submitBtn').textContent = 'Simpan Perubahan';
            document.getElementById('formTitle').textContent = 'Edit Kontak';
            document.getElementById('cancelBtn').style.display = 'inline-block';
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        document.getElementById('cancelBtn').addEventListener('click', resetForm);

        async function doDelete(id) {
            if (!confirm('Yakin ingin menghapus kontak ini?')) return;

            try {
                const res = await fetch('/contacts/' + id, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    }
                });

                const result = await res.json();

                if (!res.ok) {
                    showToast(result.message || 'Gagal menghapus data.', true);
                    return;
                }

                showToast(result.message, false);

                // Kalau kontak yang dihapus adalah kontak yang sedang diedit
                // di form, kembalikan form ke posisi awal (mode "Tambah").
                const editingId = document.getElementById('contactId').value;
                if (editingId && parseInt(editingId) === id) {
                    resetForm();
                }

                loadContacts();

            } catch (err) {
                showToast('Tidak bisa terhubung ke server.', true);
            }
        }

        loadContacts();
    </script>
</body>
</html>