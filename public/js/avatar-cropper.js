/**
 * File: avatar-cropper.js
 * Location: /public/js/avatar-cropper.js
 * 
 * Purpose:
 * Provides client-side image cropping and compression for avatar uploads.
 * Uses the HTML5 Canvas API to resize and compress images before upload,
 * bypassing server-side file size limits.
 * 
 * Architectural Role:
 * Acts as a presentation-layer enhancement. Intercepts file selection,
 * renders a preview on a canvas, applies user-controlled zoom/pan transforms,
 * and exports a compressed JPEG blob for submission via FormData.
 */
'use strict';

document.addEventListener('DOMContentLoaded', () => {
    /**
     * Guard clause: Only initialise if the avatar cropper UI exists on this page.
     * Prevents errors when this script is loaded globally but the page is not the profile.
     */
    const canvas = document.getElementById('crop-preview-canvas');
    if (!canvas) return;

    const fileInput = document.getElementById('avatar-file-input');
    const triggerBtn = document.getElementById('upload-trigger-btn');
    const modal = document.getElementById('avatar-crop-modal');
    const cancelBtn = document.getElementById('cancel-crop-btn');
    const saveBtn = document.getElementById('save-crop-btn');
    const ctx = canvas.getContext('2d');
    const zoomSlider = document.getElementById('crop-zoom');
    const panXSlider = document.getElementById('crop-pan-x');
    const panYSlider = document.getElementById('crop-pan-y');
    const csrfTokenInput = document.getElementById('csrf-token-input');

    let originalImage = null;

    /**
     * Opens the native file picker when the trigger button is clicked.
     */
    if (triggerBtn) {
        triggerBtn.addEventListener('click', () => fileInput.click());
    }

    /**
     * Handles file selection. Validates MIME type, reads the file as a data URL,
     * and initialises the cropper modal once the image has loaded.
     */
    if (fileInput) {
        fileInput.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (!file) return;

            const allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
            if (!allowedTypes.includes(file.type)) {
                alert('Please select a JPEG, PNG, or WebP image.');
                return;
            }

            const reader = new FileReader();
            reader.onload = (event) => {
                originalImage = new Image();
                originalImage.onload = () => {
                    zoomSlider.value = 1;
                    panXSlider.value = 0;
                    panYSlider.value = 0;
                    modal.style.display = 'flex';
                    drawCanvas();
                };
                originalImage.src = event.target.result;
            };
            reader.readAsDataURL(file);
        });
    }

    /**
     * Redraws the canvas whenever a slider value changes.
     */
    [zoomSlider, panXSlider, panYSlider].forEach(slider => {
        slider.addEventListener('input', drawCanvas);
    });

    /**
     * drawCanvas
     * Renders the original image onto the preview canvas using the current
     * zoom and pan values. Uses an "object-fit: cover" calculation to ensure
     * the image fills the square canvas without distortion.
     */
    function drawCanvas() {
        if (!originalImage) return;

        const width = canvas.width;
        const height = canvas.height;
        const zoom = parseFloat(zoomSlider.value);
        const panX = parseFloat(panXSlider.value);
        const panY = parseFloat(panYSlider.value);

        ctx.clearRect(0, 0, width, height);
        ctx.fillStyle = '#000';
        ctx.fillRect(0, 0, width, height);

        const imgRatio = originalImage.width / originalImage.height;
        let drawWidth, drawHeight;

        if (imgRatio > 1) {
            drawHeight = height;
            drawWidth = height * imgRatio;
        } else {
            drawWidth = width;
            drawHeight = width / imgRatio;
        }

        drawWidth *= zoom;
        drawHeight *= zoom;

        const x = (width - drawWidth) / 2 + panX;
        const y = (height - drawHeight) / 2 + panY;

        ctx.drawImage(originalImage, x, y, drawWidth, drawHeight);
    }

    /**
     * Closes the modal and resets the file input when cancelled.
     */
    if (cancelBtn) {
        cancelBtn.addEventListener('click', () => {
            modal.style.display = 'none';
            fileInput.value = '';
        });
    }

    /**
     * Handles the "Apply & Upload" action. Renders the final image to a
     * hidden 512x512 canvas, exports it as a compressed JPEG blob, and
     * submits it to the server via fetch with the CSRF token.
     */
    if (saveBtn) {
        saveBtn.addEventListener('click', () => {
            if (!originalImage) return;

            const outputSize = 512;
            const outputCanvas = document.createElement('canvas');
            outputCanvas.width = outputSize;
            outputCanvas.height = outputSize;
            const outputCtx = outputCanvas.getContext('2d');
            const scaleFactor = outputSize / canvas.width;

            outputCtx.fillStyle = '#000';
            outputCtx.fillRect(0, 0, outputSize, outputSize);

            const imgRatio = originalImage.width / originalImage.height;
            let drawWidth, drawHeight;

            if (imgRatio > 1) {
                drawHeight = outputSize;
                drawWidth = outputSize * imgRatio;
            } else {
                drawWidth = outputSize;
                drawHeight = outputSize / imgRatio;
            }

            const zoom = parseFloat(zoomSlider.value);
            const panX = parseFloat(panXSlider.value) * scaleFactor;
            const panY = parseFloat(panYSlider.value) * scaleFactor;

            drawWidth *= zoom;
            drawHeight *= zoom;

            const x = (outputSize - drawWidth) / 2 + panX;
            const y = (outputSize - drawHeight) / 2 + panY;

            outputCtx.drawImage(originalImage, x, y, drawWidth, drawHeight);

            outputCanvas.toBlob((blob) => {
                if (blob) {
                    const formData = new FormData();
                    formData.append('avatar', blob, 'avatar.jpg');
                    formData.append('update_avatar', '1');
                    if (csrfTokenInput) {
                        formData.append('csrf_token', csrfTokenInput.value);
                    }

                    saveBtn.textContent = 'Uploading...';
                    saveBtn.disabled = true;

                    fetch('profile.php', { method: 'POST', body: formData })
                        .then(response => {
                            if (response.ok) {
                                window.location.reload();
                            } else {
                                throw new Error('Upload failed');
                            }
                        })
                        .catch(error => {
                            console.error('Upload error:', error);
                            alert('Network error during upload.');
                            saveBtn.textContent = 'Apply & Upload';
                            saveBtn.disabled = false;
                        });
                }
            }, 'image/jpeg', 0.85);
        });
    }
});