<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>CSV Upload Dashboard</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <style>
        .upload-zone {
            border: 2px dashed #dee2e6;
            border-radius: 8px;
            padding: 2rem;
            text-align: center;
            background-color: #f8f9fa;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .upload-zone:hover {
            border-color: #0d6efd;
            background-color: #e3f2fd;
        }
        
        .upload-zone.dragover {
            border-color: #0d6efd;
            background-color: #e3f2fd;
            transform: scale(1.02);
        }
        
        .progress-container {
            display: none;
        }
        
        .status-badge {
            font-size: 0.75rem;
        }
        
        .file-upload-item {
            transition: all 0.3s ease;
        }
        
        .file-upload-item.updated {
            background-color: #fff3cd;
        }
        
        .table-responsive {
            max-height: 500px;
            overflow-y: auto;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <h1 class="mb-4">
                    <i class="bi bi-cloud-upload"></i>
                    CSV Upload Dashboard
                </h1>
            </div>
        </div>
        
        <!-- Upload Section -->
        <div class="row mb-5">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-file-earmark-arrow-up"></i>
                            Upload CSV File
                        </h5>
                    </div>
                    <div class="card-body">
                        <form id="uploadForm" enctype="multipart/form-data">
                            <div class="upload-zone" id="uploadZone">
                                <i class="bi bi-cloud-arrow-up fs-1 text-muted mb-3"></i>
                                <p class="mb-2">Drop your CSV file here or click to browse</p>
                                <p class="text-muted small">Maximum file size: 100MB</p>
                                <input type="file" id="fileInput" name="file" accept=".csv,.txt" style="display: none;">
                            </div>
                            
                            <div class="progress-container mt-3">
                                <div class="progress">
                                    <div class="progress-bar" role="progressbar" style="width: 0%"></div>
                                </div>
                                <div class="mt-2">
                                    <small id="uploadStatus">Uploading...</small>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary mt-3" id="uploadBtn" disabled>
                                <i class="bi bi-upload"></i>
                                Upload File
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Upload History -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bi bi-clock-history"></i>
                            Upload History
                        </h5>
                        <button class="btn btn-outline-primary btn-sm" id="refreshBtn">
                            <i class="bi bi-arrow-clockwise"></i>
                            Refresh
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Filename</th>
                                        <th>Status</th>
                                        <th>Progress</th>
                                        <th>Records</th>
                                        <th>Created</th>
                                        <th>Completed</th>
                                    </tr>
                                </thead>
                                <tbody id="uploadsTableBody">
                                    @foreach($uploads as $upload)
                                    <tr class="file-upload-item" data-id="{{ $upload->id }}">
                                        <td>
                                            <i class="bi bi-file-earmark-text me-2"></i>
                                            {{ $upload->original_name }}
                                        </td>
                                        <td>
                                            <span class="badge status-badge 
                                                @if($upload->status === 'completed') bg-success
                                                @elseif($upload->status === 'failed') bg-danger
                                                @elseif($upload->status === 'processing') bg-warning
                                                @else bg-secondary
                                                @endif">
                                                {{ ucfirst($upload->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($upload->total_records > 0)
                                                <div class="progress" style="height: 20px;">
                                                    <div class="progress-bar" style="width: {{ $upload->progress_percentage }}%">
                                                        {{ $upload->progress_percentage }}%
                                                    </div>
                                                </div>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <small>
                                                @if($upload->total_records > 0)
                                                    {{ $upload->processed_records }}/{{ $upload->total_records }}
                                                    @if($upload->failed_records > 0)
                                                        <span class="text-danger">({{ $upload->failed_records }} failed)</span>
                                                    @endif
                                                @else
                                                    -
                                                @endif
                                            </small>
                                        </td>
                                        <td>
                                            <small>{{ $upload->created_at->format('M j, Y H:i') }}</small>
                                        </td>
                                        <td>
                                            <small>
                                                {{ $upload->completed_at ? $upload->completed_at->format('M j, Y H:i') : '-' }}
                                            </small>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Laravel Echo and Pusher -->
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <script>
        // CSRF Token setup
        window.Laravel = {
            csrfToken: document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        };

        // WebSocket connection for real-time updates
        const pusher = new Pusher('{{ env('REVERB_APP_KEY') }}', {
            wsHost: '{{ env('REVERB_HOST', 'localhost') }}',
            wsPort: {{ env('REVERB_PORT', 8080) }},
            wssPort: {{ env('REVERB_PORT', 8080) }},
            forceTLS: '{{ env('REVERB_SCHEME') }}' === 'https',
            enabledTransports: ['ws', 'wss'],
            cluster: 'mt1'
        });

        const channel = pusher.subscribe('file-processing');
        
        channel.bind('file.processing.update', function(data) {
            updateUploadRow(data);
        });

        // File upload functionality
        const uploadZone = document.getElementById('uploadZone');
        const fileInput = document.getElementById('fileInput');
        const uploadForm = document.getElementById('uploadForm');
        const uploadBtn = document.getElementById('uploadBtn');
        const progressContainer = document.querySelector('.progress-container');
        const progressBar = document.querySelector('.progress-bar');
        const uploadStatus = document.getElementById('uploadStatus');

        // File input change
        fileInput.addEventListener('change', function() {
            if (this.files.length > 0) {
                uploadBtn.disabled = false;
                uploadBtn.innerHTML = '<i class="bi bi-upload"></i> Upload ' + this.files[0].name;
            } else {
                uploadBtn.disabled = true;
                uploadBtn.innerHTML = '<i class="bi bi-upload"></i> Upload File';
            }
        });

        // Drag and drop functionality
        uploadZone.addEventListener('click', () => fileInput.click());

        uploadZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadZone.classList.add('dragover');
        });

        uploadZone.addEventListener('dragleave', () => {
            uploadZone.classList.remove('dragover');
        });

        uploadZone.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadZone.classList.remove('dragover');
            
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                fileInput.files = files;
                fileInput.dispatchEvent(new Event('change'));
            }
        });

        // Form submission
        uploadForm.addEventListener('submit', async function(e) {
            console.log('Form submit event triggered');
            e.preventDefault();
            
            if (!fileInput.files.length) {
                console.log('No files selected');
                return;
            }

            console.log('Selected file:', fileInput.files[0].name);
            console.log('CSRF token:', window.Laravel.csrfToken);

            const formData = new FormData();
            formData.append('file', fileInput.files[0]);
            formData.append('_token', window.Laravel.csrfToken);

            uploadBtn.disabled = true;
            progressContainer.style.display = 'block';
            progressBar.style.width = '0%';
            uploadStatus.textContent = 'Uploading...';

            try {
                console.log('Sending request to /api/upload');
                const response = await fetch('/api/upload', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': window.Laravel.csrfToken
                    }
                });

                console.log('Response status:', response.status);
                console.log('Response ok:', response.ok);

                const result = await response.json();
                console.log('Response data:', result);

                if (result.success) {
                    progressBar.style.width = '100%';
                    uploadStatus.textContent = 'Upload complete! Processing...';
                    
                    // Reset form after 2 seconds
                    setTimeout(() => {
                        resetForm();
                        loadUploads();
                    }, 2000);
                } else {
                    throw new Error(result.message || 'Upload failed');
                }
            } catch (error) {
                console.error('Upload error:', error);
                uploadStatus.textContent = 'Upload failed: ' + error.message;
                uploadBtn.disabled = false;
            }
        });

        function resetForm() {
            uploadForm.reset();
            uploadBtn.disabled = true;
            uploadBtn.innerHTML = '<i class="bi bi-upload"></i> Upload File';
            progressContainer.style.display = 'none';
        }

        function updateUploadRow(data) {
            const row = document.querySelector(`tr[data-id="${data.id}"]`);
            if (!row) {
                // Add new row if it doesn't exist
                loadUploads();
                return;
            }

            // Update status badge
            const statusBadge = row.querySelector('.badge');
            statusBadge.className = 'badge status-badge';
            statusBadge.textContent = data.status.charAt(0).toUpperCase() + data.status.slice(1);
            
            if (data.status === 'completed') {
                statusBadge.classList.add('bg-success');
            } else if (data.status === 'failed') {
                statusBadge.classList.add('bg-danger');
            } else if (data.status === 'processing') {
                statusBadge.classList.add('bg-warning');
            } else {
                statusBadge.classList.add('bg-secondary');
            }

            // Update progress
            const progressDiv = row.querySelector('.progress');
            if (progressDiv && data.total_records > 0) {
                const progressBar = progressDiv.querySelector('.progress-bar');
                progressBar.style.width = data.progress_percentage + '%';
                progressBar.textContent = data.progress_percentage + '%';
            }

            // Update records
            const recordsCell = row.cells[3];
            if (data.total_records > 0) {
                let recordsText = `${data.processed_records}/${data.total_records}`;
                if (data.failed_records > 0) {
                    recordsText += ` <span class="text-danger">(${data.failed_records} failed)</span>`;
                }
                recordsCell.innerHTML = `<small>${recordsText}</small>`;
            }

            // Update completed time
            if (data.completed_at) {
                const completedCell = row.cells[5];
                const date = new Date(data.completed_at);
                completedCell.innerHTML = `<small>${date.toLocaleDateString('en-US', {
                    month: 'short',
                    day: 'numeric',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                })}</small>`;
            }

            // Highlight updated row
            row.classList.add('updated');
            setTimeout(() => row.classList.remove('updated'), 3000);
        }

        async function loadUploads() {
            try {
                const response = await fetch('/api/uploads');
                const result = await response.json();
                
                if (result.success) {
                    updateUploadsTable(result.data);
                }
            } catch (error) {
                console.error('Error loading uploads:', error);
            }
        }

        function updateUploadsTable(uploads) {
            const tbody = document.getElementById('uploadsTableBody');
            tbody.innerHTML = '';
            
            uploads.forEach(upload => {
                const row = createUploadRow(upload);
                tbody.appendChild(row);
            });
        }

        function createUploadRow(upload) {
            const row = document.createElement('tr');
            row.className = 'file-upload-item';
            row.setAttribute('data-id', upload.id);
            
            const statusClass = upload.status === 'completed' ? 'bg-success' :
                              upload.status === 'failed' ? 'bg-danger' :
                              upload.status === 'processing' ? 'bg-warning' : 'bg-secondary';
            
            const progressHtml = upload.total_records > 0 ?
                `<div class="progress" style="height: 20px;">
                    <div class="progress-bar" style="width: ${upload.progress_percentage}%">
                        ${upload.progress_percentage}%
                    </div>
                </div>` : '<span class="text-muted">-</span>';
            
            const recordsHtml = upload.total_records > 0 ?
                `${upload.processed_records}/${upload.total_records}` +
                (upload.failed_records > 0 ? ` <span class="text-danger">(${upload.failed_records} failed)</span>` : '') :
                '-';
            
            const createdDate = new Date(upload.created_at);
            const completedDate = upload.completed_at ? new Date(upload.completed_at) : null;
            
            row.innerHTML = `
                <td>
                    <i class="bi bi-file-earmark-text me-2"></i>
                    ${upload.filename}
                </td>
                <td>
                    <span class="badge status-badge ${statusClass}">
                        ${upload.status.charAt(0).toUpperCase() + upload.status.slice(1)}
                    </span>
                </td>
                <td>${progressHtml}</td>
                <td><small>${recordsHtml}</small></td>
                <td><small>${createdDate.toLocaleDateString('en-US', {
                    month: 'short', day: 'numeric', year: 'numeric',
                    hour: '2-digit', minute: '2-digit'
                })}</small></td>
                <td><small>${completedDate ? completedDate.toLocaleDateString('en-US', {
                    month: 'short', day: 'numeric', year: 'numeric',
                    hour: '2-digit', minute: '2-digit'
                }) : '-'}</small></td>
            `;
            
            return row;
        }

        // Refresh button
        document.getElementById('refreshBtn').addEventListener('click', loadUploads);

        // Load uploads on page load
        document.addEventListener('DOMContentLoaded', loadUploads);
    </script>
</body>
</html>