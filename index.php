<?php
// archive.php
session_start();

// Check if the user is logged in. If not, redirect to the login page.
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File Explorer</title>
    <style>
        /* Add the CSS styles for the file explorer here */
        body {
            font-family: Arial, sans-serif;
            background-color: #222;
        }

        h1 {
            text-align: center;
            padding: 20px;
            background-color: #f5deb3;
            color: #222;
        }

        div {
            color: #f5deb3;
        }

        .file-explorer {
            display: flex;
            margin: 20px;
        }

        .folders {
            width: 18%;
            height: 80vh;
            /* 80% of the viewport height */
            background-color: #333;
            border: 1px solid #223;
            padding: 10px;
            overflow-y: auto;
        }

        .folder {
            padding: 5px;
            cursor: pointer;
        }

        .images {
            flex: 1;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            grid-gap: 10px;
            justify-items: center;
        }

        .images img {
            max-width: 100%;
            max-height: 200px;
            border: 1px solid #ccc;
            border-radius: 5px;
            cursor: pointer;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.8);
        }

        .modal-content {
            margin: 10% auto;
            padding: 20px;
            max-width: 800px;
        }

        .modal-img {
            width: 100%;
            max-height: 90vh;
            display: block;
            margin: 0 auto;
            cursor: zoom-out;
        }

        /* Navigation Arrows */
        .nav-arrows {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }

        .nav-arrows button {
            background-color: #ccc;
            border: none;
            padding: 5px 10px;
            margin-right: 10px;
            cursor: pointer;
        }

        .nav-arrows button:hover {
            background-color: #ddd;
        }

        .thumbnail {
            width: 256px;
            height: 256px;
            object-fit: cover;
        }
    </style>
</head>

<body>
    <h1>Archive</h1>

    <div class="file-explorer" id="fileExplorer">
        <div class="folders" id="folderContainer">
            <!-- Folders will be dynamically added here -->
        </div>
        <div class="images" id="imageContainer">
            <!-- Images will be dynamically added here -->
        </div>
    </div>

    <!-- Modal for image zoom -->
    <div id="imageModal" class="modal">
        <span class="modal-content">
            <img id="zoomedImage" class="modal-img" alt="Zoomed Image">
        </span>
    </div>

    <div class="nav-arrows">
        <button id="goBackBtn" disabled>&larr; Go Back</button>
        <button id="goUpBtn" disabled>&uarr; Go Up</button>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const folderContainer = document.getElementById('folderContainer');
            const imageContainer = document.getElementById('imageContainer');
            const goBackBtn = document.getElementById('goBackBtn');
            const goUpBtn = document.getElementById('goUpBtn');
            let currentFolder = 'UI';
            let folderHistory = ['UI'];

            function createFolderElement(folderName, subfolders) {
                const folderElement = document.createElement('div');
                folderElement.className = 'folder';
                folderElement.textContent = folderName;

                if (subfolders.length > 0) {
                    const subfolderList = document.createElement('ul');
                    subfolders.forEach(subfolder => {
                        const subfolderItem = document.createElement('li');
                        subfolderItem.appendChild(createFolderElement(subfolder, []));
                        subfolderItem.onclick = () => navigateToSubfolder(folderName + '/' + subfolder);
                        subfolderList.appendChild(subfolderItem);
                    });
                    folderElement.appendChild(subfolderList);
                }

                folderElement.onclick = () => navigateToSubfolder(folderName);
                return folderElement;
            }

            function addImageToGallery(imgUrl, fileName) {
                const imageItem = document.createElement('div');
                imageItem.className = 'image-item';

                const imgElement = document.createElement('img');
                imgElement.src = imgUrl;
                imgElement.alt = 'Image';
                imgElement.className = 'thumbnail'; // Add the thumbnail class
                imgElement.onclick = () => openImage(imgUrl);

                const fileNameElement = document.createElement('div');
                fileNameElement.textContent = fileName;
                fileNameElement.className = 'file-name';

                imageItem.appendChild(imgElement);
                imageItem.appendChild(fileNameElement);
                imageContainer.appendChild(imageItem);
            }

            async function loadImages(folderName = 'UI') {
                try {
                    const response = await fetch(`get_images.php?folder=${encodeURIComponent(folderName)}`);
                    const data = await response.json();
                    folderContainer.innerHTML = '';
                    folderContainer.appendChild(createFolderElement(folderName, data.subfolders));
                    imageContainer.innerHTML = '';
                    data.images.forEach(imgFileName => {
                        const imgUrl = folderName + '/' + imgFileName;
                        addImageToGallery(imgUrl, imgFileName);
                    });
                    currentFolder = folderName;
                    folderHistory.push(folderName);
                    updateNavigationButtons();
                } catch (error) {
                    console.error('Error loading images:', error);
                }
            }

            function navigateToSubfolder(subfolder) {
                loadImages(subfolder);
            }

            function goBack() {
                if (folderHistory.length > 1) {
                    folderHistory.pop(); // Remove current folder from history
                    const prevFolder = folderHistory.pop(); // Get the previous folder
                    loadImages(prevFolder);
                }
            }

            function goUp() {
                if (currentFolder !== 'UI') {
                    const parentFolder = currentFolder.split('/').slice(0, -1).join('/');
                    loadImages(parentFolder);
                }
            }

            function updateNavigationButtons() {
                goBackBtn.disabled = folderHistory.length <= 1;
                goUpBtn.disabled = currentFolder === 'UI';
            }

            function openImage(imageUrl) {
                const modal = document.getElementById('imageModal');
                const modalImg = document.getElementById('zoomedImage');
                modal.style.display = 'block';
                modalImg.src = imageUrl;

                // Close the modal when the user clicks outside the image
                modal.onclick = function () {
                    modal.style.display = 'none';
                };
            }

            goBackBtn.onclick = goBack;
            goUpBtn.onclick = goUp;

            loadImages();
        });
    </script>
</body>

</html>