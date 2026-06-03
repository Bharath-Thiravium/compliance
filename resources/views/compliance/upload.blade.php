<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compliance Engine - Upload</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #333; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input, textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; }
        button { background: #007bff; color: white; padding: 12px 24px; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; }
        button:hover { background: #0056b3; }
        .message { padding: 15px; margin-bottom: 20px; border-radius: 4px; }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .info { background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
        .loading { display: none; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Compliance Engine - Upload Form</h1>
        
        <div id="message"></div>

        <form id="uploadForm">
            <div class="form-group">
                <label>File Upload</label>
                <input type="file" id="fileInput" required>
            </div>

            <div class="form-group">
                <label>Dataset Type</label>
                <select id="datasetType" required>
                    <option value="">Select...</option>
                    <option value="employees">Employees</option>
                    <option value="payroll">Payroll</option>
                    <option value="attendance">Attendance</option>
                </select>
            </div>

            <button type="submit" id="uploadBtn">Upload</button>
            <span id="loading" class="loading">Uploading...</span>
        </form>
    </div>

    <script>
        const API_URL = 'http://localhost:8000';

        function showMessage(message, type) {
            const messageDiv = document.getElementById('message');
            messageDiv.innerHTML = `<div class="message ${type}">${message}</div>`;
        }

        async function getCsrfToken() {
            try {
                showMessage('Getting CSRF token...', 'info');
                
                const response = await fetch(`${API_URL}/api/csrf-token`, {
                    method: 'GET',
                    credentials: 'include',
                    headers: {
                        'Accept': 'application/json',
                    }
                });

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }

                const data = await response.json();
                return data.csrf_token;
            } catch (error) {
                showMessage(`Failed to get CSRF token: ${error.message}`, 'error');
                throw error;
            }
        }

        document.getElementById('uploadForm').addEventListener('submit', async (e) => {
            e.preventDefault();

            const file = document.getElementById('fileInput').files[0];
            const datasetType = document.getElementById('datasetType').value;
            const btn = document.getElementById('uploadBtn');
            const loading = document.getElementById('loading');

            if (!file || !datasetType) {
                showMessage('Please select file and dataset type', 'error');
                return;
            }

            try {
                loading.style.display = 'inline';
                btn.disabled = true;

                const csrfToken = await getCsrfToken();

                const formData = new FormData();
                formData.append('file', file);
                formData.append('dataset_type', datasetType);

                showMessage('Uploading file...', 'info');

                const response = await fetch(`${API_URL}/compliance/data/upload`, {
                    method: 'POST',
                    credentials: 'include',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: formData
                });

                const data = await response.json();

                if (response.ok) {
                    showMessage('Upload successful!', 'success');
                    document.getElementById('uploadForm').reset();
                } else {
                    showMessage(`Upload failed: ${data.message || 'Unknown error'}`, 'error');
                }
            } catch (error) {
                showMessage(`Error: ${error.message}`, 'error');
            } finally {
                loading.style.display = 'none';
                btn.disabled = false;
            }
        });

        // Test CSRF endpoint on page load
        window.addEventListener('load', async () => {
            try {
                showMessage('Testing CSRF endpoint...', 'info');
                const token = await getCsrfToken();
                showMessage(`Ready. CSRF Token: ${token.substring(0, 10)}...`, 'success');
            } catch (error) {
                showMessage(`CSRF test failed: ${error.message}`, 'error');
            }
        });
    </script>
</body>
</html>
