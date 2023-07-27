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
            subfolders.forEach((subfolder) => {
                const subfolderItem = document.createElement('li');
                subfolderItem.appendChild(createFolderElement(`${folderName}/${subfolder.folderName}`, subfolder.subfolders));
                subfolderItem.onclick = () => loadImages(`${folderName}/${subfolder.folderName}`);
                subfolderList.appendChild(subfolderItem);
            });
            folderElement.appendChild(subfolderList);
        }

        folderElement.onclick = () => loadImages(folderName);
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
            const response = await fetch(`https://raw.githubusercontent.com/JMJAJ/database-ui-tof/main/UI/${encodeURIComponent(folderName)}/data.json`);
            const data = await response.json();
            folderContainer.innerHTML = '';
            folderContainer.appendChild(createFolderElement(folderName, data.subfolders));
            imageContainer.innerHTML = '';
            data.images.forEach((imageData) => {
                const imgUrl = `https://raw.githubusercontent.com/JMJAJ/database-ui-tof/main/UI/${encodeURIComponent(folderName)}/${encodeURIComponent(imageData.fileName)}`;
                addImageToGallery(imgUrl, imageData.fileName);
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
