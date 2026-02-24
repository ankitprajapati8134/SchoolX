<div class="content-wrapper">
    <div class="page_container">
        <div class="container">
            <div class="upload-section">
                <input type="file" id="fileInput" multiple style="display: none;">
                <button class="upload-btn" onclick="document.getElementById('fileInput').click()">Upload</button>
            </div>

            <input type="text" id="searchInput" placeholder="Search files..." onkeyup="searchFiles()">

            <div class="grid-container">
                <h3>Uploaded Files</h3>
                <div id="selectionContainer" style="display: none;">
                    <p><span id="selectedCount">0</span> file(s) selected</p>
                    <button id="deleteSelectedBtn" onclick="deleteSelectedFiles()">Delete Selected</button>
                </div>

                <div id="categorizedFiles">
                    <div class="category" id="imagesCategory">
                        <h4 onclick="toggleCollapse('imageGrid', 'imageArrow')">
                            Images <span id="imageArrow">&#9650;</span>
                        </h4>
                        <div class="grid" id="imageGrid"></div>
                    </div>

                    <div class="category" id="videosCategory">
                        <h4 onclick="toggleCollapse('videoGrid', 'videoArrow')">
                            Videos <span id="videoArrow">&#9650;</span>
                        </h4>
                        <div class="grid" id="videoGrid"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal for Viewing Files -->
        <div id="fileModal" class="modal">
            <div class="modal-content" onclick="event.stopPropagation();">
                <span class="close" onclick="closeModal()">&times;</span>
                <div id="fileViewer"></div>
            </div>
        </div>

    </div>
</div>


<script>
    document.addEventListener("DOMContentLoaded", function() {
        fetchGalleryMedia();
        // Close modal on outside click
        document.getElementById("fileModal").addEventListener("click", function() {
            closeModal();
        });
    });



    function fetchGalleryMedia() {
        fetch("<?= base_url('schools/fetchGalleryMedia') ?>")
            .then(response => response.json())
            .then(data => {
                displayMedia(data.images, "imageGrid", "image");
                displayMedia(data.videos, "videoGrid", "video");
            })
            .catch(error => console.error("Error fetching gallery:", error));
    }

    // Function to show modal for viewing media
    // function displayMedia(mediaArray, gridId, mediaType) {
    //     const grid = document.getElementById(gridId);
    //     grid.innerHTML = "";

    //     mediaArray.forEach(media => {
    //         const fileName = extractFileName(media.url);
    //         const formattedDate = new Date(media.timestamp * 1000).toLocaleString();

    //         let mediaElement = "";
    //         if (mediaType === "image") {
    //             mediaElement = `
    //             <img src="${media.url}" alt="${fileName}" onclick="viewFile('${media.url}', '${mediaType}')" />
    //         `;
    //         } else if (mediaType === "video") {
    //             const thumbUrl = media.thumbnail || media.url;
    //             const duration = media.duration || "";
    //             mediaElement = `
    //             <div class="video-thumbnail-wrapper" onclick="viewFile('${media.url}', '${mediaType}')">
    //                 <img src="${thumbUrl}" alt="${fileName}" class="video-thumb" />
    //                 <span class="video-duration">${duration}</span>
    //             </div>
    //         `;
    //         }

    //         const gridItem = document.createElement("div");
    //         gridItem.classList.add("grid-item");

    //         gridItem.innerHTML = `
    //         <div class="media-top">
    //             <input type="checkbox" class="media-checkbox" onclick="updateSelectionCount()" data-url="${media.url}" />
    //             ${mediaElement}
    //         </div>

    //         <div class="file-info">
    //             <p class="file-name">${fileName}</p>
    //             <p class="upload-time">${formattedDate}</p>
    //             <div class="button-container">
    //                 <button class="view-btn" onclick="viewFile('${media.url}', '${mediaType}')">View</button>
    //                 <button class="delete-btn" onclick="deleteFile('${media.url}', this)">Delete</button>
    //             </div>
    //         </div>
    //     `;

    //         grid.appendChild(gridItem);
    //     });
    // }

    function displayMedia(mediaArray, gridId, mediaType) {
        const grid = document.getElementById(gridId);
        grid.innerHTML = "";

        mediaArray.forEach(media => {
            const fileName = extractFileName(media.url);
            const formattedDate = new Date(media.timestamp * 1000).toLocaleString();

            let mediaElement = "";
            if (mediaType === "image") {
                mediaElement = `
                <img src="${media.url}" alt="${fileName}" onclick="viewFile('${media.url}', '${mediaType}')" />
            `;
            } else if (mediaType === "video") {
                const thumbUrl = media.thumbnail || media.url;
                const duration = media.duration || "";
                mediaElement = `
                <div class="video-thumbnail-wrapper" onclick="viewFile('${media.url}', '${mediaType}')">
                    <img src="${thumbUrl}" alt="${fileName}" class="video-thumb" />
                    <span class="video-duration">${duration}</span>
                </div>
            `;
            }

            const gridItem = document.createElement("div");
            gridItem.classList.add("grid-item");

            gridItem.innerHTML = `
            <div class="media-top">
                <input type="checkbox" class="media-checkbox" onclick="updateSelectionCount()" data-url="${media.url}" />
                ${mediaElement}
            </div>

            <div class="file-info">
                <p class="file-name">${fileName}</p>
                <p class="upload-time">${formattedDate}</p>
                <div class="button-container">
                    <button class="delete-btn" onclick="deleteFile('${media.url}', this)">Delete</button>
                </div>
            </div>
        `;

            grid.appendChild(gridItem);
        });
    }





    // function viewFile(url, mediaType) {
    //     const modal = document.getElementById("fileModal");
    //     const viewer = document.getElementById("fileViewer");

    //     if (mediaType === "image") {
    //         viewer.innerHTML = `<img src="${url}" alt="Media Preview">`;
    //     } else if (mediaType === "video") {
    //         viewer.innerHTML = `
    //         <video controls autoplay>
    //             <source src="${url}" type="video/mp4">
    //             Your browser does not support the video tag.
    //         </video>
    //     `;
    //     }

    //     modal.style.display = "flex";
    // }


    function viewFile(url, mediaType) {
        const modal = document.getElementById("fileModal");
        const viewer = document.getElementById("fileViewer");

        if (mediaType === "image") {
            // viewer.innerHTML = `<img src="${url}" class="img-responsive" style="max-width:100%; max-height:80vh;" alt="Media Preview">`;
            viewer.innerHTML = `<img src="${url}" class="modal-img" alt="Media Preview">`;

        } else if (mediaType === "video") {
            viewer.innerHTML = `
            <video controls autoplay style="width:100%; max-height:80vh; border-radius:8px; background:#000;">
                <source src="${url}" type="video/mp4">
                Your browser does not support the video tag.
            </video>`;
        }

        modal.classList.add("show");

        document.body.style.overflow = "hidden"; // Prevent background scroll
    }


    // Function to close the modal
    // function closeModal() {
    //     const modal = document.getElementById("fileModal");

    //     modal.style.display = "none";
    //     document.getElementById("fileViewer").innerHTML = "";
    // }
    function closeModal() {
        const modal = document.getElementById("fileModal");
        modal.classList.remove("show");
        setTimeout(() => {

            document.getElementById("fileViewer").innerHTML = "";
            document.body.style.overflow = "auto"; // Re-enable scroll
        }, 300); // Match transition duration
    }

    document.addEventListener("DOMContentLoaded", function() {
        document.getElementById("fileModal").addEventListener("click", function() {
            closeModal();
        });
    });



    // Extract file name from URL
    function extractFileName(url) {
        try {
            const decodedUrl = decodeURIComponent(url);
            const segments = decodedUrl.split("/");
            return segments[segments.length - 1].split("?")[0]; // Extract filename before query params
        } catch (error) {
            console.error("Error extracting file name:", error);
            return "Unknown File";
        }
    }

    // document.getElementById('fileInput').addEventListener('change', function(event) {
    //     const file = event.target.files[0];
    //     if (!file) return;

    //     const formData = new FormData();
    //     formData.append('file', file);

    //     // Detect file type
    //     const fileType = file.type.startsWith('image') ? '1' : '2';
    //     formData.append('type', fileType);

    //     const fileInput = document.getElementById('fileInput');
    //     fileInput.disabled = true;

    //     console.log("Sending File:", file.name, "Type:", fileType);

    //     fetch('<?= base_url("schools/uploadMedia") ?>', {
    //             method: 'POST',
    //             body: formData
    //         })
    //         .then(response => {
    //             if (!response.ok) throw new Error("HTTP error " + response.status);
    //             return response.json();
    //         })
    //         .then(data => {
    //             console.log("Server Response:", data);

    //             if (data.status === 'success') {
    //                 alert('File uploaded successfully!');

    //                 document.getElementById('fileInput').value = '';
    //                 fetchGalleryMedia();
    //             } else {
    //                 // alert('Upload failed: ' + data.message);
    //                 alert('Upload failed: ' + (data.message || 'Unknown error'));
    //             }
    //         })
    //         .catch(error => {
    //             console.error('Error during upload:', error);
    //             alert('An unexpected error occurred during upload.');
    //         })
    //         .finally(() => {
    //             // fileInput.disabled = false;
    //             document.getElementById('fileInput').disabled = false;
    //         });
    // });


    document.getElementById('fileInput').addEventListener('change', function(event) {
        const files = event.target.files;
        if (!files.length) return;

        const fileInput = document.getElementById('fileInput');
        fileInput.disabled = true;

        const uploadPromises = [];

        Array.from(files).forEach(file => {
            const formData = new FormData();
            formData.append('file', file);

            // Detect file type
            const fileType = file.type.startsWith('image') ? '1' : '2';
            formData.append('type', fileType);

            console.log("Uploading File:", file.name, "Type:", fileType);

            const uploadPromise = fetch('<?= base_url("schools/uploadMedia") ?>', {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    if (!response.ok) throw new Error("HTTP error " + response.status);
                    return response.json();
                })
                .then(data => {
                    console.log("Server Response:", data);
                    if (data.status !== 'success') {
                        alert(`Failed to upload ${file.name}: ` + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error(`Upload failed for ${file.name}:`, error);
                    alert(`Upload failed for ${file.name}`);
                });

            uploadPromises.push(uploadPromise);
        });

        Promise.all(uploadPromises)
            .then(() => {
                alert('File(s) uploaded successfully!');
                document.getElementById('fileInput').value = '';
                fetchGalleryMedia(); // Refresh the media grid
            })
            .finally(() => {
                fileInput.disabled = false;
            });
    });



    function searchFiles() {
        let input = document.getElementById("searchInput").value.toLowerCase();
        let items = document.querySelectorAll(".grid-item");
        items.forEach(item => {
            let text = item.innerText.toLowerCase();
            item.style.display = text.includes(input) ? "block" : "none";
        });
    }

    function toggleCollapse(gridId, arrowId) {
        let grid = document.getElementById(gridId);
        let arrow = document.getElementById(arrowId);
        if (grid.style.display === "none") {
            grid.style.display = "grid";
            arrow.innerHTML = "&#9650;";
        } else {
            grid.style.display = "none";
            arrow.innerHTML = "&#9660;";
        }
    }

    function updateSelectionCount() {
        let checkboxes = document.querySelectorAll('.media-checkbox:checked');
        let selectedCount = checkboxes.length;

        document.getElementById('selectedCount').innerText = selectedCount;

        if (selectedCount > 0) {
            document.getElementById('selectionContainer').style.display = "block";
        } else {
            document.getElementById('selectionContainer').style.display = "none";
        }
    }

    function deleteSelectedFiles() {
        let checkboxes = document.querySelectorAll('.media-checkbox:checked');
        checkboxes.forEach(checkbox => {
            let fileUrl = checkbox.dataset.url;
            deleteFile(fileUrl, checkbox.closest('.grid-item'));
        });

        // Hide selection container after deletion
        document.getElementById('selectionContainer').style.display = "none";
    }


    function deleteFile(fileUrl, buttonElement) {
        fetch(`<?= base_url("schools/deleteMedia") ?>?url=${encodeURIComponent(fileUrl)}`, {
                method: 'DELETE'
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    const gridItem = buttonElement.closest('.grid-item');
                    if (gridItem) {
                        gridItem.remove(); // ✅ properly removes the full UI card
                    }
                    updateSelectionCount();
                } else {
                    alert("Failed to delete file: " + data.message);
                }
            })
            .catch(error => console.error('Error:', error));
    }
</script>


<style>
    body {
        font-family: 'Roboto', Arial, sans-serif;
        margin: 0;
        padding: 0;
        background-color: #f8f9fa;
    }

    .container {
        padding: 2rem;
        max-width: 1200px;
        margin: auto;
    }

    .upload-section {
        margin-bottom: 2rem;
    }

    .upload-btn {
        background-color: #28a745;
        color: white;
        padding: 0.8rem 1.5rem;
        border-radius: 5px;
        cursor: pointer;
        border: none;
    }

    #searchInput {
        width: 100%;
        padding: 10px;
        margin-bottom: 1rem;
        border: 1px solid #ced4da;
        border-radius: 4px;
    }

    .grid-container {
        margin-top: 2rem;
    }

    .category {
        margin-bottom: 1rem;
    }

    .category h4 {
        background: #007bff;
        color: white;
        padding: 10px 15px;
        border-radius: 5px;
        cursor: pointer;
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 16px;
    }

    .grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        gap: 1.5rem;
    }

    .grid-item {
        background: white;
        padding: 1rem;
        text-align: center;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        position: relative;
        display: flex;
        flex-direction: column;
        align-items: center;
        transition: transform 0.2s ease-in-out, box-shadow 0.2s;
    }

    .grid-item:hover {
        transform: scale(1.03);
        box-shadow: 0 6px 14px rgba(0, 0, 0, 0.15);
    }

    .grid-item img,
    .grid-item video {
        width: 100%;
        height: 180px;
        object-fit: cover;
        border-radius: 6px;
        cursor: pointer;
    }

    .file-info {
        width: 100%;
        text-align: center;
        padding: 10px 5px;
        background: rgba(0, 0, 0, 0.05);
        border-radius: 0 0 8px 8px;
        margin-top: 10px;
        font-size: 14px;
    }

    .file-name {
        font-weight: bold;
        margin: 6px 0 2px;
        word-wrap: break-word;
        font-size: 14px;
        color: #333;
    }

    .upload-time {
        font-size: 12px;
        color: #666;
    }

    .button-container {
        margin-top: 10px;
    }

    .view-btn,
    .delete-btn {
        display: inline-block;
        padding: 6px 12px;
        margin: 4px 6px 0 6px;
        border-radius: 4px;
        font-size: 13px;
        border: none;
        cursor: pointer;
        transition: background 0.2s;
    }

    .view-btn {
        background-color: #007bff;
        color: white;
    }

    .view-btn:hover {
        background-color: #0056b3;
    }

    .delete-btn {
        background-color: #dc3545;
        color: white;
        display: none;
    }

    .grid-item:hover .delete-btn {
        display: inline-block;
    }

    .delete-btn:hover {
        background-color: #b02a37;
    }

    /* Modal Styling */
    .modal {
        display: flex;
        /* ← ✅ Always use flex */
        justify-content: center;
        align-items: center;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.85);
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.3s ease;
    }

    .modal.show {
        opacity: 1;
        pointer-events: all;
    }

    .modal-content {
        background: transparent;
        padding: 0;
        border-radius: 10px;
        max-width: 90vw;
        max-height: 90vh;
        display: flex;
        justify-content: center;
        align-items: center;
        position: relative;
        box-shadow: 0 0 15px rgba(0, 0, 0, 0.6);
    }

    .modal-content img,
    .modal-content video {
        max-width: 100%;
        max-height: 80vh;
        object-fit: contain;
        border-radius: 5px;
        display: block;
        margin: auto;
    }

    /* .modal-content img {
        max-width: 100%;
        max-height: 80vh;
        border-radius: 5px;
    }

    .modal-content video {
        width: 100%;
        height: auto;
        max-height: 80vh;
        object-fit: contain;
        background: #000;
        border-radius: 8px;
    } */

    .modal-img {
        /* max-width: 100%;
        max-height: 80vh;
        width: auto;
        height: auto;
        object-fit: contain;
        border-radius: 8px;
        display: block;
        margin: auto; */
        max-width: 100%;
        max-height: 80vh;
        object-fit: contain;
        border-radius: 8px;
        display: block;
    }



    .close {
        position: absolute;
        top: 20px;
        right: 30px;
        font-size: 40px;
        color: #ffffff;
        z-index: 1001;
        cursor: pointer;
    }

    #selectionContainer {
        display: none;
        background-color: #f1f1f1;
        padding: 10px 15px;
        border: 1px solid #ccc;
        border-radius: 5px;
        display: flex;
        justify-content: flex-end;
        align-items: center;
        gap: 15px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    #selectedCount {
        font-weight: bold;
        color: #333;
    }

    #deleteSelectedBtn {
        background-color: #dc3545;
        color: white;
        border: none;
        padding: 8px 15px;
        cursor: pointer;
        border-radius: 5px;
        font-weight: bold;
        transition: 0.3s;
    }

    #deleteSelectedBtn:hover {
        background-color: #c82333;
    }

    .video-thumbnail-wrapper {
        position: relative;
        width: 100%;
    }

    .video-thumb {
        width: 100%;
        height: 180px;
        border-radius: 4px;
        object-fit: cover;
    }

    .video-duration {
        position: absolute;
        bottom: 6px;
        right: 8px;
        background-color: rgba(0, 0, 0, 0.75);
        color: white;
        padding: 2px 6px;
        font-size: 12px;
        border-radius: 3px;
    }

    .media-top {
        position: relative;
        width: 100%;
    }

    .media-top input[type="checkbox"] {
        position: absolute;
        top: 8px;
        left: 8px;
        z-index: 2;
        transform: scale(1.2);
    }
</style>