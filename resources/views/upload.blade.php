@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto mt-10 p-6 bg-white shadow-md rounded">
        <form id="uploadForm" class="space-y-4 mb-8" enctype="multipart/form-data">
            <input type="file" name="csv_file" class="block w-full border p-2" required>
            <div id="progressContainer" class="hidden mt-4">
                <div class="w-full bg-gray-200 h-4 rounded relative overflow-hidden">
                    <div id="uploadProgress" class="bg-green-600 h-4 w-0 text-white text-center text-xs leading-4 transition-all duration-300 ease-out">
                        0%
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-4 mt-4">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">
                    Upload CSV
                </button>

                <div id="clearHistoryWrapper" class="hidden">
                    <button id="clearHistoryBtn" type="button" class="bg-red-500 text-white px-4 py-2 rounded">
                        Clear History
                    </button>
                </div>
            </div>
        </form>

        <h2 class="font-semibold text-lg mb-2">Upload History</h2>
        <div class="overflow-x-auto w-full">
            <table class="min-w-full table-auto text-sm text-left text-gray-700" id="uploadTable">
                <thead class="bg-gray-100 font-medium">
                <tr>
                    <th class="px-3 py-2 w-48 whitespace-nowrap cursor-pointer" onclick="sortTable(0, this)">
                        Time <span class="ml-1 sort-icon"></span>
                    </th>
                    <th class="px-3 py-2 min-w-[300px] max-w-[500px] truncate cursor-pointer" onclick="sortTable(1, this)">
                        File Name <span class="ml-1 sort-icon"></span>
                    </th>
                    <th class="px-3 py-2 w-28 cursor-pointer" onclick="sortTable(2, this)">
                        Status <span class="ml-1 sort-icon"></span>
                    </th>
                </tr>
                </thead>
                <tbody id="uploadHistory" class="divide-y divide-gray-200">
                <!-- Filled by JS -->
                </tbody>
            </table>
        </div>
    </div>

    <script>
        // To check and set the guest_id if not available
        let guestId = localStorage.getItem('guest_id');
        if (!guestId) {
            guestId = 'guest_' + Math.random().toString(36).substring(2, 10);
            localStorage.setItem('guest_id', guestId);
        }

        // To load the file histories
        async function loadHistory() {
            const res = await fetch('/api/uploads?guest_id=' + guestId);
            const { data } = await res.json();
            const tbody = document.getElementById('uploadHistory');
            tbody.innerHTML = '';

            data.forEach(upload => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
            <td class="px-4 py-2 whitespace-nowrap">
                ${upload.created_at} <span class="text-xs text-gray-400">(${upload.created_human})</span>
            </td>
            <td class="px-4 py-2 truncate max-w-[300px]" title="${upload.file_name}">
                ${upload.file_name}
            </td>
            <td class="px-4 py-2 font-semibold ${
                    upload.status === 'completed' ? 'text-green-600' :
                        upload.status === 'failed' ? 'text-red-600' :
                            'text-yellow-600'
                }">${upload.status.charAt(0).toUpperCase() + upload.status.slice(1)}</td>`;
                tbody.appendChild(tr);
            });

            const clearBtnWrapper = document.getElementById('clearHistoryWrapper');
            if (data.length > 0) {
                clearBtnWrapper.classList.remove('hidden');
            } else {
                clearBtnWrapper.classList.add('hidden');
            }
        }

        // To make the table sortable
        function sortTable(columnIndex, headerElement) {
            const table = document.getElementById('uploadTable');
            const tbody = table.tBodies[0];
            const rows = Array.from(tbody.rows);

            const isAsc = headerElement.getAttribute('data-sort') !== 'asc';
            headerElement.setAttribute('data-sort', isAsc ? 'asc' : 'desc');

            document.querySelectorAll('.sort-icon').forEach(icon => {
                icon.textContent = '';
            });

            const icon = headerElement.querySelector('.sort-icon');
            icon.textContent = isAsc ? '▲' : '▼';

            rows.sort((a, b) => {
                const aText = a.cells[columnIndex].innerText.trim().toLowerCase();
                const bText = b.cells[columnIndex].innerText.trim().toLowerCase();
                return isAsc ? aText.localeCompare(bText) : bText.localeCompare(aText);
            });

            // Append sorted rows
            rows.forEach(row => tbody.appendChild(row));
        }

        document.addEventListener('DOMContentLoaded', function () {
            loadHistory();

            // To submit the form for processing
            document.getElementById('uploadForm').addEventListener('submit', async function(e) {
                e.preventDefault();

                const progressContainer = document.getElementById('progressContainer');
                const progressBar = document.getElementById('uploadProgress');
                progressContainer.classList.remove('hidden');
                progressBar.style.width = '0%';
                progressBar.textContent = '0%';

                const formData = new FormData(this);
                formData.append('guest_id', guestId);

                const xhr = new XMLHttpRequest();
                xhr.open('POST', '/api/upload-csv', true);

                xhr.upload.addEventListener('progress', function(e) {
                    if (e.lengthComputable) {
                        const percent = Math.round((e.loaded / e.total) * 100);
                        progressBar.style.width = percent + '%';
                        progressBar.textContent = percent + '%';
                    }
                });

                xhr.onload = function() {
                    if (xhr.status === 200) {
                        progressBar.style.width = '100%';
                        progressBar.textContent = '100%';
                        document.getElementById('uploadForm').reset();
                    } else {
                        alert('Upload failed.');
                        progressBar.style.backgroundColor = 'red';
                        progressBar.textContent = 'Failed';
                    }
                    loadHistory();
                };

                xhr.send(formData);
            });

            // To reset guest_id and clear uploaded file history
            document.getElementById('clearHistoryBtn')?.addEventListener('click', async function () {
                if (confirm('Are you sure you want to clear your upload history?')) {
                    localStorage.removeItem('guest_id');
                    await fetch('/api/uploads/clear', {
                        method: 'POST',
                        headers: { 'X-Guest-ID': guestId },
                    });
                    document.getElementById('uploadHistory').innerHTML = '';
                    location.reload();
                }
            });

            // Setup Echo only if it's available
            window.addEventListener('DOMContentLoaded', (event) => {
                if (window.Echo) {
                    if (typeof Echo !== 'undefined') {
                        Echo.channel('csv-status.' + guestId)
                            .listen('.CsvProcessed', e => {
                                console.log('✅ CSV processed event received:', e);
                                loadHistory();
                            });
                    } else {
                        console.warn('Echo is not defined');
                    }
                }
            });
        });
    </script>
@endsection
