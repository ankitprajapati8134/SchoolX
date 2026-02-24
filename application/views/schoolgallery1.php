<div class="content-wrapper">
    <div class="container">
        <div class="upload-section">
            <input type="file" id="fileInput" style="display: none;" onchange="handleFileUpload(event)">
            <button class="upload-btn" onclick="document.getElementById('fileInput').click()">Upload</button>
        </div>

        <div class="table-container">
            <h3>Uploaded Documents</h3>
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>File Name</th>
                        <th>Description</th>
                        <th>Type</th>
                        <th>Size</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="documentTable">
                    <!-- Dynamic content will appear here -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- File Edit Modal -->
<div class="modal" id="editModal">
    <div class="modal-content">
        <h3>Edit File Details</h3>
        <label>File Name:</label>
        <input type="text" id="editFileName" />

        <label>Description:</label>
        <textarea id="editDescription"></textarea>

        <button class="save-btn" onclick="saveFileDetails()">Save</button>
        <button class="back-btn" onclick="closeEditModal()">Cancel</button>
    </div>
</div>

<!-- File View Modal -->
<div class="modal" id="fileModal">
    <div class="modal-content">
        <iframe id="fileViewer" src=""></iframe>
        <button class="back-btn" onclick="closeModal()">Back</button>
    </div>
</div>

<script>
    function addImageToGrid(date, fileName, fileURL, fileType, fileSize) {
    const grid = document.querySelector('#gridContainer .grid');
    const gridItem = document.createElement('div');
    gridItem.classList.add('grid-item');
    gridItem.innerHTML = `
        <img src="${fileURL}" alt="${fileName}" />
        <p><strong>${fileName}</strong></p>
        <p>${fileSize}</p>
    `;
    grid.appendChild(gridItem);
}
let currentEditingRow = null;

function handleFileUpload(event) {
    const file = event.target.files[0];
    if (file) {
        const date = new Date().toLocaleDateString();
        const fileName = file.name;
        const fileType = file.type || 'Unknown';
        const fileSize = (file.size / 1024).toFixed(2) + ' KB';
        const fileURL = URL.createObjectURL(file);

        const table = document.getElementById('documentTable');
        const row = table.insertRow();
        row.innerHTML = `
            <td>${date}</td>
            <td class="file-name">${fileName}</td>
            <td class="file-desc">-</td>
            <td>${fileType}</td>
            <td>${fileSize}</td>
            <td>
                <button class="view-btn" onclick="viewFile('${fileURL}')">View</button>
                <button class="edit-btn" onclick="editFileDetails(this)">Edit</button>
                <button class="delete-btn" onclick="deleteFile(this)">Delete</button>
            </td>
        `;
    }
}

function deleteFile(button) {
    const row = button.closest("tr"); // Get the row containing the clicked button
    row.remove(); // Remove the row from the table
}


function editFileDetails(button) {
    currentEditingRow = button.closest("tr"); // Store the row being edited
    const fileName = currentEditingRow.querySelector(".file-name").textContent;
    const fileDesc = currentEditingRow.querySelector(".file-desc").textContent;

    document.getElementById("editFileName").value = fileName;
    document.getElementById("editDescription").value = fileDesc === "-" ? "" : fileDesc;

    document.getElementById("editModal").style.display = "flex";
}

function saveFileDetails() {
    if (currentEditingRow) {
        const newFileName = document.getElementById("editFileName").value;
        const newDescription = document.getElementById("editDescription").value;

        currentEditingRow.querySelector(".file-name").textContent = newFileName;
        currentEditingRow.querySelector(".file-desc").textContent = newDescription || "-";

        closeEditModal();
    }
}

function closeEditModal() {
    document.getElementById("editModal").style.display = "none";
}

function viewFile(url) {
    const modal = document.getElementById("fileModal");
    const viewer = document.getElementById("fileViewer");
    viewer.src = url;
    modal.style.display = "flex";
}

function closeModal() {
    const modal = document.getElementById("fileModal");
    modal.style.display = "none";
    document.getElementById("fileViewer").src = "";
}
</script>

<style>
body {
        font-family: Arial, sans-serif;
        background-color: #f8f9fa;
        margin: 0;

    }

    .container {
        width: 80%;
        padding-right: 20px;
        /* Add right padding */
        border: 1px solid #ccc;
        border-radius: 5px;
        padding-bottom: 20px;
        padding-top: 20px;

        font-size: 16px;
    }

.upload-section {
    display: flex;
    justify-content: flex-start;
    align-items: center;
    margin-bottom: 2rem;
}

.upload-btn {
    background-color: #28a745;
    color: white;
    border: none;
    padding: 0.8rem 1.5rem;
    font-size: 1.7rem;
    cursor: pointer;
    border-radius: 5px;
    transition: all 0.3s ease-in-out;
}

.upload-btn:hover {
    background-color: #218838;
}

/* Table styles */
.table-container {
    background: white;
    border-radius: 8px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    padding: 1.5rem;
}

.table-container h3 {
    margin-top: 0;
    color: #007bff;
    font-size: 2rem;
    border-bottom: 2px solid #007bff;
    display: inline-block;
    padding-bottom: 0.5rem;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 1rem;
}

table th, table td {
    padding: 1rem;
    text-align: left;
    border: 1px solid #dee2e6;
}

table th {
    background-color: #f1f1f1;
    font-size: 1.5rem;
}

table td {
    background-color: #ffffff;
    font-size: 1.4rem;
}

.view-btn, .edit-btn {
    background-color: #007bff;
    color: white;
    border: none;
    padding: 0.5rem 1rem;
    font-size: 1.3rem;
    cursor: pointer;
    border-radius: 5px;
    transition: all 0.3s ease-in-out;
    margin-right: 5px;
}

.edit-btn {
    background-color: #ffc107;
    color: black;
}

.view-btn:hover {
    background-color: #0056b3;
}

.edit-btn:hover {
    background-color: #e0a800;
}

/* Modal Styles */
.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    justify-content: center;
    align-items: center;
    z-index: 1000;
}

.modal-content {
    background: white;
    padding: 1.5rem;
    border-radius: 10px;
    max-width: 400px;
    text-align: center;
}

.modal-content input, .modal-content textarea {
    width: 100%;
    margin: 10px 0;
    padding: 10px;
    font-size: 1rem;
}

.save-btn, .back-btn {
    background-color: #28a745;
    color: white;
    border: none;
    padding: 0.5rem 1.5rem;
    cursor: pointer;
    border-radius: 5px;
    margin-top: 1rem;
}

.back-btn {
    background-color: #dc3545;
}

.save-btn:hover {
    background-color: #218838;
}

.back-btn:hover {
    background-color: #bd2130;
}
.grid-container {
    margin-top: 2rem;
}

.grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: 1rem;
}

.grid-item {
    text-align: center;
    background: #ffffff;
    padding: 1rem;
    border-radius: 5px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.grid-item img {
    max-width: 100%;
    border-radius: 5px;
}
.delete-btn {
    background-color: #dc3545;
    color: white;
    border: none;
    padding: 0.5rem 1rem;
    font-size: 1.3rem;
    cursor: pointer;
    border-radius: 5px;
    transition: all 0.3s ease-in-out;
    /* margin-left: 5px; */
}

.delete-btn:hover {
    background-color: #bd2130;
}
</style>
