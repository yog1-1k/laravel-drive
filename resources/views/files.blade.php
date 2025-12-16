<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Drive Files</title>

    <!-- ICON -->
    <link rel="icon"
        href="https://static.vecteezy.com/system/resources/previews/017/395/378/non_2x/google-drive-icons-free-png.png">

    <!-- TAILWIND -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- BOOTSTRAP ICONS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
</head>

<body class="bg-gradient-to-br from-slate-100 to-slate-200 min-h-screen p-6">

    <!-- ================= HEADER ================= -->
    <div class="max-w-7xl mx-auto mb-6">
        <div class="bg-white rounded-2xl shadow p-6 flex justify-between items-center">
            <div>
                <h2 class="text-3xl font-bold text-slate-800 flex items-center gap-2">
                    <i class="bi bi-folder-fill text-blue-600"></i>
                    Drive Files
                </h2>
                <p class="text-slate-500 text-sm">Kelola file Google Drive Anda</p>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto space-y-6">

        <!-- ALERT -->
        @if(session('result'))
            <div class="p-4 rounded-xl bg-emerald-100 text-emerald-800 shadow flex gap-2 items-center">
                <i class="bi bi-check-circle-fill"></i>
                {{ session('result')['message'] ?? 'Berhasil!' }}
            </div>
        @endif

        <!-- ================= UPLOAD ================= -->
        <div class="bg-white rounded-2xl shadow p-6">
            <form action="/upload" method="POST" enctype="multipart/form-data"
                class="flex flex-col md:flex-row gap-4 items-center">
                @csrf
                <input type="file" name="file"
                    class="w-full border-2 border-dashed rounded-xl p-4 text-slate-600 focus:outline-none">
                <button type="submit"
                    class="w-full md:w-48 py-3 rounded-xl bg-blue-600 text-white font-semibold hover:bg-blue-700 transition flex items-center justify-center gap-2">
                    <i class="bi bi-upload"></i> Upload File
                </button>
            </form>
        </div>

        <!-- ================= SEARCH & SORT ================= -->
        <div class="bg-white rounded-2xl shadow p-4 flex flex-col md:flex-row gap-4">
            <div class="relative w-full">
                <i class="bi bi-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                <input type="text" id="searchInput" placeholder="Cari nama file..."
                    class="border rounded-xl p-3 pl-11 w-full focus:ring focus:ring-blue-200"
                    onkeyup="filterTable()">
            </div>

            <select id="sortSelect" onchange="sortTable()"
                class="border rounded-xl p-3 w-full md:w-60 focus:ring focus:ring-blue-200">
                <option value="">Urutkan</option>
                <option value="name-asc">Nama A → Z</option>
                <option value="name-desc">Nama Z → A</option>
                <option value="size-asc">Ukuran ↑</option>
                <option value="size-desc">Ukuran ↓</option>
                <option value="date-asc">Tanggal ↑</option>
                <option value="date-desc">Tanggal ↓</option>
            </select>
        </div>

        @php
            function formatSize($bytes)
            {
                if ($bytes >= 1073741824) return number_format($bytes / 1073741824, 2) . ' GB';
                if ($bytes >= 1048576) return number_format($bytes / 1048576, 2) . ' MB';
                if ($bytes >= 1024) return number_format($bytes / 1024, 2) . ' KB';
                return $bytes . ' bytes';
            }

            function getFileIcon($name)
            {
                $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                return match ($ext) {
                    'pdf' => 'bi-file-earmark-pdf-fill text-red-500',
                    'doc', 'docx' => 'bi-file-earmark-word-fill text-blue-500',
                    'xls', 'xlsx' => 'bi-file-earmark-excel-fill text-green-600',
                    'jpg', 'jpeg', 'png', 'gif' => 'bi-file-earmark-image-fill text-purple-500',
                    'zip', 'rar', '7z' => 'bi-file-earmark-zip-fill text-yellow-600',
                    'mp3', 'wav' => 'bi-file-earmark-music-fill text-pink-500',
                    'mp4', 'mkv', 'avi' => 'bi-file-earmark-play-fill text-indigo-500',
                    default => 'bi-file-earmark-fill text-slate-500'
                };
            }
        @endphp

        <!-- ================= TABLE ================= -->
       <!-- ================= TABLE ================= -->
<div class="bg-white rounded-2xl shadow overflow-visible relative">
    <table id="fileTable" class="w-full text-sm">
        <thead class="bg-slate-50 text-slate-600 uppercase text-xs">
            <tr>
                <th class="p-4 text-left">File</th>
                <th class="p-4 text-left">Ukuran</th>
                <th class="p-4 text-left">Dibuat</th>
                <th class="p-4 text-center">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($files as $file)
                <tr class="border-t hover:bg-slate-50 transition"
                    data-name="{{ strtolower($file['name']) }}"
                    data-size="{{ $file['size'] }}"
                    data-createdat="{{ strtotime($file['createdAt']) }}">

                    <td class="p-4 flex items-center gap-3 font-medium text-slate-800">
                        <i class="bi {{ getFileIcon($file['name']) }} text-xl"></i>
                        {{ $file['name'] }}
                    </td>

                    <td class="p-4">
                        <span class="px-3 py-1 rounded-full bg-blue-100 text-blue-700 text-xs font-semibold">
                            {{ formatSize($file['size']) }}
                        </span>
                    </td>

                    <td class="p-4 text-slate-500">
                        {{ date('d M Y H:i', strtotime($file['createdAt'])) }}
                    </td>

                    <!-- AKSI -->
                    <td class="p-4 text-center">
                        <div class="relative inline-block">
                            <button onclick="toggleMenu('{{ $file['id'] }}', event)"
                                class="w-9 h-9 rounded-full hover:bg-slate-200 flex items-center justify-center">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>

                            <!-- DROPDOWN -->
                            <div id="menu-{{ $file['id'] }}"
                                class="hidden absolute right-0 mt-2 bg-white border shadow-xl rounded-xl w-52 z-50 py-2">
                                <a href="{{ $file['url'] }}" target="_blank"
                                    class="flex items-center gap-2 px-4 py-2 hover:bg-slate-100">
                                    <i class="bi bi-eye"></i> View
                                </a>
                                <a href="{{ $file['downloadUrl'] }}" target="_blank"
                                    class="flex items-center gap-2 px-4 py-2 hover:bg-slate-100">
                                    <i class="bi bi-download"></i> Download
                                </a>
                                <button
                                    onclick="openRenameModal('{{ $file['id'] }}', '{{ $file['name'] }}')"
                                    class="w-full text-left flex items-center gap-2 px-4 py-2 hover:bg-slate-100">
                                    <i class="bi bi-pencil"></i> Rename
                                </button>
                                <form action="/file/delete" method="POST"
                                    onsubmit="return confirm('Yakin hapus file?')">
                                    @csrf
                                    <input type="hidden" name="file_id" value="{{ $file['id'] }}">
                                    <button type="submit"
                                        class="w-full text-left flex items-center gap-2 px-4 py-2 text-red-600 hover:bg-red-50">
                                        <i class="bi bi-trash"></i> Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

    </div>

    <!-- ================= MODAL RENAME ================= -->
    <div id="renameModal" class="hidden fixed inset-0 bg-black/40 flex justify-center items-center">
        <form action="/file/rename" method="POST"
            class="bg-white rounded-2xl p-6 w-80 shadow-xl">
            @csrf
            <h3 class="text-lg font-semibold mb-4 flex items-center gap-2">
                <i class="bi bi-pencil-square"></i> Rename File
            </h3>
            <input type="hidden" name="file_id" id="renameFileId">
            <input type="text" name="new_name" id="renameInput"
                class="w-full border rounded-xl p-3 mb-4 focus:ring focus:ring-blue-200">
            <div class="flex justify-end gap-3">
                <button type="button" onclick="closeModal()"
                    class="px-4 py-2 rounded-xl bg-slate-200">Batal</button>
                <button type="submit"
                    class="px-4 py-2 rounded-xl bg-blue-600 text-white">Simpan</button>
            </div>
        </form>
    </div>

    <!-- ================= SCRIPT ================= -->
    <script>
        function toggleMenu(id, event) {
            event.stopPropagation();
            const menu = document.getElementById("menu-" + id);

            document.querySelectorAll("[id^='menu-']").forEach(m => {
                if (m !== menu) m.classList.add("hidden");
            });

            menu.classList.toggle("hidden");
        }

        document.addEventListener("click", () => {
            document.querySelectorAll("[id^='menu-']").forEach(menu => {
                menu.classList.add("hidden");
            });
        });

        function openRenameModal(id, name) {
            const dot = name.lastIndexOf('.');
            let base = name;
            let ext = '';

            if (dot !== -1) {
                base = name.substring(0, dot);
                ext = name.substring(dot);
            }

            document.getElementById("renameFileId").value = id;
            const input = document.getElementById("renameInput");
            input.value = base;
            input.dataset.ext = ext;
            document.getElementById("renameModal").classList.remove("hidden");
        }

        document.querySelector("#renameModal form").addEventListener("submit", function () {
            const input = document.getElementById("renameInput");
            input.value = input.value + (input.dataset.ext || '');
        });

        function closeModal() {
            document.getElementById("renameModal").classList.add("hidden");
        }

        function filterTable() {
            const value = document.getElementById("searchInput").value.toLowerCase();
            document.querySelectorAll("#fileTable tbody tr").forEach(row => {
                row.style.display = row.dataset.name.includes(value) ? "" : "none";
            });
        }

        function sortTable() {
            const type = document.getElementById("sortSelect").value;
            const tbody = document.querySelector("#fileTable tbody");
            const rows = Array.from(tbody.querySelectorAll("tr"));

            rows.sort((a, b) => {
                if (type === 'name-asc') return a.dataset.name.localeCompare(b.dataset.name);
                if (type === 'name-desc') return b.dataset.name.localeCompare(a.dataset.name);
                if (type === 'size-asc') return a.dataset.size - b.dataset.size;
                if (type === 'size-desc') return b.dataset.size - a.dataset.size;
                if (type === 'date-asc') return a.dataset.createdat - b.dataset.createdat;
                if (type === 'date-desc') return b.dataset.createdat - a.dataset.createdat;
                return 0;
            });

            tbody.innerHTML = '';
            rows.forEach(r => tbody.appendChild(r));
        }
    </script>

</body>
</html>
