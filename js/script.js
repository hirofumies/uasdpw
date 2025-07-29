// Validasi form dan interaktivitas
document.addEventListener('DOMContentLoaded', function() {
    console.log('Website Upload Tugas - JavaScript Loaded');
    
    // Inisialisasi semua fungsi
    initFormValidation();
    initFileUpload();
    initAlerts();
    initAnimations();
});

// Fungsi validasi form
function initFormValidation() {
    const forms = document.querySelectorAll('form');
    
    forms.forEach(form => {
        const inputs = form.querySelectorAll('input[required], select[required]');
        
        // Real-time validation
        inputs.forEach(input => {
            input.addEventListener('blur', function() {
                validateField(this);
            });
            
            input.addEventListener('input', function() {
                if (this.classList.contains('error')) {
                    validateField(this);
                }
            });
        });
        
        // Form submission validation
        form.addEventListener('submit', function(e) {
            let isValid = true;
            const requiredFields = form.querySelectorAll('[required]');
            
            requiredFields.forEach(field => {
                if (!validateField(field)) {
                    isValid = false;
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                showAlert('Mohon lengkapi semua field yang wajib diisi!', 'error');
                
                // Focus pada field pertama yang error
                const firstError = form.querySelector('.error input, .error select');
                if (firstError) {
                    firstError.focus();
                }
            } else {
                // Show loading state
                const submitBtn = form.querySelector('button[type="submit"]');
                if (submitBtn) {
                    const originalText = submitBtn.innerHTML;
                    submitBtn.innerHTML = '<span class="loading"></span> Memproses...';
                    submitBtn.disabled = true;
                    
                    // Reset jika ada error (untuk demo)
                    setTimeout(() => {
                        if (submitBtn.disabled) {
                            submitBtn.innerHTML = originalText;
                            submitBtn.disabled = false;
                        }
                    }, 5000);
                }
            }
        });
    });
}

// Fungsi validasi individual field
function validateField(field) {
    const formGroup = field.closest('.form-group');
    const value = field.value.trim();
    let isValid = true;
    let errorMessage = '';
    
    // Reset state
    formGroup.classList.remove('error', 'success');
    
    // Validasi required
    if (field.hasAttribute('required') && !value) {
        isValid = false;
        errorMessage = 'Field ini wajib diisi';
    }
    
    // Validasi khusus berdasarkan type
    if (value && field.type === 'text') {
        if (field.name === 'nim') {
            if (!/^[0-9]+$/.test(value)) {
                isValid = false;
                errorMessage = 'NIM harus berupa angka';
            } else if (value.length < 6 || value.length > 20) {
                isValid = false;
                errorMessage = 'NIM harus 6-20 karakter';
            }
        }
        
        if (field.name === 'nama') {
            if (value.length < 2) {
                isValid = false;
                errorMessage = 'Nama minimal 2 karakter';
            } else if (!/^[a-zA-Z\s]+$/.test(value)) {
                isValid = false;
                errorMessage = 'Nama hanya boleh huruf dan spasi';
            }
        }
    }
    
    // Tampilkan hasil validasi
    if (isValid) {
        formGroup.classList.add('success');
        removeFieldError(formGroup);
    } else {
        formGroup.classList.add('error');
        showFieldError(formGroup, errorMessage);
    }
    
    return isValid;
}

// Fungsi untuk menampilkan error pada field
function showFieldError(formGroup, message) {
    removeFieldError(formGroup);
    
    const errorElement = document.createElement('small');
    errorElement.className = 'error-message';
    errorElement.style.color = '#dc3545';
    errorElement.style.fontWeight = '500';
    errorElement.textContent = message;
    
    formGroup.appendChild(errorElement);
}

// Fungsi untuk menghapus error pada field
function removeFieldError(formGroup) {
    const existingError = formGroup.querySelector('.error-message');
    if (existingError) {
        existingError.remove();
    }
}

// Fungsi untuk handling file upload
function initFileUpload() {
    const fileInput = document.getElementById('fileToUpload');
    if (!fileInput) return;
    
    fileInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (!file) return;
        
        const maxSize = 5 * 1024 * 1024; // 5MB
        const allowedTypes = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'text/plain',
            'image/jpeg',
            'image/jpg', 
            'image/png'
        ];
        
        const allowedExtensions = ['pdf', 'doc', 'docx', 'txt', 'jpg', 'jpeg', 'png'];
        const fileExtension = file.name.split('.').pop().toLowerCase();
        
        // Reset styling
        fileInput.style.borderColor = '#ddd';
        removeFileError();
        
        // Validasi ukuran file
        if (file.size > maxSize) {
            showFileError('Ukuran file terlalu besar. Maksimal 5MB.');
            fileInput.value = '';
            return;
        }
        
        // Validasi tipe file
        if (!allowedTypes.includes(file.type) && !allowedExtensions.includes(fileExtension)) {
            showFileError('Format file tidak didukung. Gunakan: PDF, DOC, DOCX, TXT, JPG, PNG');
            fileInput.value = '';
            return;
        }
        
        // Tampilkan info file yang dipilih
        showFileInfo(file);
    });
}

// Fungsi untuk menampilkan error file
function showFileError(message) {
    const fileInput = document.getElementById('fileToUpload');
    const formGroup = fileInput.closest('.form-group');
    
    fileInput.style.borderColor = '#dc3545';
    
    removeFileError();
    
    const errorElement = document.createElement('small');
    errorElement.className = 'file-error';
    errorElement.style.color = '#dc3545';
    errorElement.style.fontWeight = '500';
    errorElement.textContent = message;
    
    formGroup.appendChild(errorElement);
}

// Fungsi untuk menghapus error file
function removeFileError() {
    const existingError = document.querySelector('.file-error');
    if (existingError) {
        existingError.remove();
    }
}

// Fungsi untuk menampilkan info file
function showFileInfo(file) {
    const fileInput = document.getElementById('fileToUpload');
    const formGroup = fileInput.closest('.form-group');
    
    removeFileError();
    
    const infoElement = document.createElement('small');
    infoElement.className = 'file-info';
    infoElement.style.color = '#28a745';
    infoElement.style.fontWeight = '500';
    infoElement.innerHTML = `
        ✅ File dipilih: <strong>${file.name}</strong><br>
        📏 Ukuran: ${(file.size / 1024).toFixed(2)} KB<br>
        📄 Tipe: ${file.type || 'Unknown'}
    `;
    
    formGroup.appendChild(infoElement);
    fileInput.style.borderColor = '#28a745';
}

// Fungsi untuk menampilkan alert
function showAlert(message, type = 'info') {
    // Hapus alert yang ada
    const existingAlert = document.querySelector('.alert-dynamic');
    if (existingAlert) {
        existingAlert.remove();
    }
    
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type === 'error' ? 'error' : 'success'} alert-dynamic`;
    alertDiv.textContent = message;
    
    // Insert setelah header
    const header = document.querySelector('header');
    header.insertAdjacentElement('afterend', alertDiv);
    
    // Auto hide setelah 5 detik
    setTimeout(() => {
        if (alertDiv && alertDiv.parentNode) {
            alertDiv.style.opacity = '0';
            alertDiv.style.transition = 'opacity 0.5s ease';
            setTimeout(() => {
                if (alertDiv.parentNode) {
                    alertDiv.remove();
                }
            }, 500);
        }
    }, 5000);
}

// Fungsi untuk inisialisasi alert
function initAlerts() {
    const alerts = document.querySelectorAll('.alert:not(.alert-dynamic)');
    
    alerts.forEach(alert => {
        // Auto hide alert setelah 8 detik
        setTimeout(() => {
            alert.style.opacity = '0';
            alert.style.transition = 'opacity 0.5s ease';
            setTimeout(() => {
                if (alert.parentNode) {
                    alert.remove();
                }
            }, 500);
        }, 8000);
        
        // Tambahkan tombol close
        const closeBtn = document.createElement('span');
        closeBtn.innerHTML = '&times;';
        closeBtn.style.cssText = `
            float: right;
            font-size: 20px;
            font-weight: bold;
            cursor: pointer;
            margin-left: 15px;
        `;
        
        closeBtn.onclick = function() {
            alert.style.opacity = '0';
            alert.style.transition = 'opacity 0.3s ease';
            setTimeout(() => {
                if (alert.parentNode) {
                    alert.remove();
                }
            }, 300);
        };
        
        alert.appendChild(closeBtn);
    });
}

// Fungsi untuk animasi
function initAnimations() {
    // Animasi fade in untuk elemen
    const elements = document.querySelectorAll('.form-section, .info-section, .uploads-section');
    
    elements.forEach((element, index) => {
        element.style.opacity = '0';
        element.style.transform = 'translateY(20px)';
        element.style.transition = 'all 0.6s ease';
        
        setTimeout(() => {
            element.style.opacity = '1';
            element.style.transform = 'translateY(0)';
        }, index * 200);
    });
    
    // Hover effect untuk upload items
    const uploadItems = document.querySelectorAll('.upload-item');
    uploadItems.forEach(item => {
        item.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px) scale(1.02)';
        });
        
        item.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
        });
    });
}

// Fungsi utility untuk format ukuran file
function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

// Fungsi untuk konfirmasi logout
function confirmLogout() {
    return confirm('Apakah Anda yakin ingin keluar? Data sesi akan hilang.');
}

// Event listener untuk tombol logout
document.addEventListener('click', function(e) {
    if (e.target.closest('a[href*="logout=true"]')) {
        if (!confirmLogout()) {
            e.preventDefault();
        }
    }
});

// Fungsi untuk smooth scroll
function smoothScroll(target) {
    document.querySelector(target).scrollIntoView({
        behavior: 'smooth'
    });
}

// Progress bar untuk upload (simulasi)
function showUploadProgress() {
    const form = document.querySelector('form[enctype="multipart/form-data"]');
    if (!form) return;
    
    form.addEventListener('submit', function(e) {
        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn && !submitBtn.disabled) {
            // Buat progress bar
            const progressContainer = document.createElement('div');
            progressContainer.className = 'upload-progress';
            progressContainer.innerHTML = `
                <div style="background: #f0f0f0; border-radius: 10px; overflow: hidden; margin: 10px 0;">
                    <div class="progress-bar" style="
                        width: 0%; 
                        height: 20px; 
                        background: linear-gradient(45deg, #4CAF50, #45a049);
                        transition: width 0.3s ease;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        color: white;
                        font-size: 12px;
                        font-weight: bold;
                    ">0%</div>
                </div>
            `;
            
            form.appendChild(progressContainer);
            
            // Simulasi progress
            let progress = 0;
            const progressBar = progressContainer.querySelector('.progress-bar');
            
            const interval = setInterval(() => {
                progress += Math.random() * 15;
                if (progress > 90) progress = 90;
                
                progressBar.style.width = progress + '%';
                progressBar.textContent = Math.round(progress) + '%';
                
                if (progress >= 90) {
                    clearInterval(interval);
                }
            }, 200);
        }
    });
}

// Inisialisasi progress bar
showUploadProgress();

// Fungsi untuk menampilkan waktu real-time
function updateDateTime() {
    const now = new Date();
    const options = {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
    };
    
    const dateTimeString = now.toLocaleDateString('id-ID', options);
    
    // Tambahkan ke footer jika belum ada
    const footer = document.querySelector('footer');
    let timeElement = footer.querySelector('.current-time');
    
    if (!timeElement) {
        timeElement = document.createElement('p');
        timeElement.className = 'current-time';
        timeElement.style.fontSize = '0.8em';
        timeElement.style.opacity = '0.7';
        footer.appendChild(timeElement);
    }
    
    timeElement.textContent = `Waktu akses: ${dateTimeString}`;
}

// Update waktu setiap detik
setInterval(updateDateTime, 1000);
updateDateTime(); // Jalankan sekali saat load

// Fungsi untuk lazy loading gambar (jika ada)
function initLazyLoading() {
    const images = document.querySelectorAll('img[data-src]');
    
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.classList.remove('lazy');
                observer.unobserve(img);
            }
        });
    });
    
    images.forEach(img => imageObserver.observe(img));
}

// Inisialisasi lazy loading
initLazyLoading();

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    // Ctrl + U untuk focus ke upload
    if (e.ctrlKey && e.key === 'u') {
        e.preventDefault();
        const fileInput = document.getElementById('fileToUpload');
        if (fileInput) {
            fileInput.click();
        }
    }
    
    // Escape untuk close alert
    if (e.key === 'Escape') {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            if (alert.style.opacity !== '0') {
                alert.style.opacity = '0';
                alert.style.transition = 'opacity 0.3s ease';
                setTimeout(() => {
                    if (alert.parentNode) {
                        alert.remove();
                    }
                }, 300);
            }
        });
    }
});

// Console info
console.log(`
🎓 Website Upload Tugas - JavaScript Loaded Successfully!
📋 Features:
- Form Validation
- File Upload Validation  
- Real-time Feedback
- Smooth Animations
- Progress Simulation
- Keyboard Shortcuts (Ctrl+U, Escape)
- Auto-hide Alerts
- Responsive Design

💡 Tips:
- Tekan Ctrl+U untuk quick upload
- Tekan Escape untuk close alerts
- Semua validasi berjalan real-time
`);