/**
 * Scripts principais do site
 * @version 1.0
 * @author Sistema Administrativo
 */

// Esperar o DOM carregar completamente
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM carregado - scripts.js inicializado');
    
    // Inicializar todos os componentes
    initMobileMenu();
    initSmoothScroll();
    initFormValidation();
    initBackToTop();
    initGalleryLightbox();
    initContactForm();
    initStickyHeader();
    initAnimations();
    initDropdowns();
    initModals();
    
    // Verificar se estamos na área administrativa
    if (document.querySelector('.admin-container')) {
        initAdminComponents();
    }
});

/**
 * ============================================
 * FUNÇÕES GERAIS DO SITE
 * ============================================
 */

/**
 * Menu Mobile Responsivo
 */
function initMobileMenu() {
    const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
    const navMenu = document.querySelector('.nav-menu');
    
    if (mobileMenuBtn && navMenu) {
        mobileMenuBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            navMenu.classList.toggle('active');
            this.classList.toggle('active');
            
            // Alternar ícone
            const icon = this.querySelector('i');
            if (icon) {
                if (navMenu.classList.contains('active')) {
                    icon.classList.remove('fa-bars');
                    icon.classList.add('fa-times');
                } else {
                    icon.classList.remove('fa-times');
                    icon.classList.add('fa-bars');
                }
            }
        });
        
        // Fechar menu ao clicar fora
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.nav-menu') && !e.target.closest('.mobile-menu-btn')) {
                navMenu.classList.remove('active');
                mobileMenuBtn.classList.remove('active');
                
                const icon = mobileMenuBtn.querySelector('i');
                if (icon) {
                    icon.classList.remove('fa-times');
                    icon.classList.add('fa-bars');
                }
            }
        });
        
        // Fechar menu ao redimensionar para desktop
        window.addEventListener('resize', function() {
            if (window.innerWidth > 768) {
                navMenu.classList.remove('active');
                mobileMenuBtn.classList.remove('active');
                
                const icon = mobileMenuBtn.querySelector('i');
                if (icon) {
                    icon.classList.remove('fa-times');
                    icon.classList.add('fa-bars');
                }
            }
        });
    }
}

/**
 * Scroll Suave para Âncoras
 */
function initSmoothScroll() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            
            // Ignorar links que são apenas "#"
            if (href === '#' || href === '#!') return;
            
            const targetElement = document.querySelector(href);
            
            if (targetElement) {
                e.preventDefault();
                
                // Calcular posição considerando header fixo
                const headerHeight = document.querySelector('.site-header')?.offsetHeight || 80;
                const targetPosition = targetElement.getBoundingClientRect().top + window.pageYOffset - headerHeight;
                
                // Scroll suave
                window.scrollTo({
                    top: targetPosition,
                    behavior: 'smooth'
                });
                
                // Fechar menu mobile se aberto
                const navMenu = document.querySelector('.nav-menu');
                const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
                
                if (navMenu && navMenu.classList.contains('active')) {
                    navMenu.classList.remove('active');
                    if (mobileMenuBtn) {
                        mobileMenuBtn.classList.remove('active');
                        const icon = mobileMenuBtn.querySelector('i');
                        if (icon) {
                            icon.classList.remove('fa-times');
                            icon.classList.add('fa-bars');
                        }
                    }
                }
                
                // Atualizar URL sem recarregar a página (para one-page)
                if (history.pushState) {
                    history.pushState(null, null, href);
                } else {
                    location.hash = href;
                }
            }
        });
    });
}

/**
 * Validação de Formulários
 */
function initFormValidation() {
    const forms = document.querySelectorAll('form:not(.no-validate)');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!validateForm(this)) {
                e.preventDefault();
                return false;
            }
        });
    });
}

function validateForm(form) {
    let isValid = true;
    const requiredFields = form.querySelectorAll('[required]');
    
    requiredFields.forEach(field => {
        // Limpar erro anterior
        clearFieldError(field);
        
        // Validar campo vazio
        if (!field.value.trim()) {
            showFieldError(field, 'Este campo é obrigatório.');
            isValid = false;
            return;
        }
        
        // Validação específica por tipo
        switch (field.type) {
            case 'email':
                if (!isValidEmail(field.value)) {
                    showFieldError(field, 'Por favor, insira um e-mail válido.');
                    isValid = false;
                }
                break;
                
            case 'tel':
                if (!isValidPhone(field.value)) {
                    showFieldError(field, 'Por favor, insira um telefone válido.');
                    isValid = false;
                }
                break;
                
            case 'url':
                if (!isValidURL(field.value)) {
                    showFieldError(field, 'Por favor, insira uma URL válida.');
                    isValid = false;
                }
                break;
        }
        
        // Validação de tamanho mínimo
        const minLength = field.getAttribute('minlength');
        if (minLength && field.value.length < parseInt(minLength)) {
            showFieldError(field, `Mínimo de ${minLength} caracteres.`);
            isValid = false;
        }
        
        // Validação de tamanho máximo
        const maxLength = field.getAttribute('maxlength');
        if (maxLength && field.value.length > parseInt(maxLength)) {
            showFieldError(field, `Máximo de ${maxLength} caracteres.`);
            isValid = false;
        }
    });
    
    // Validação de senha (se houver campos de senha)
    const password = form.querySelector('input[type="password"][name="nova_senha"]');
    const confirmPassword = form.querySelector('input[type="password"][name="confirmar_senha"]');
    
    if (password && confirmPassword && password.value !== confirmPassword.value) {
        showFieldError(confirmPassword, 'As senhas não coincidem.');
        isValid = false;
    }
    
    return isValid;
}

function showFieldError(field, message) {
    const formGroup = field.closest('.form-group');
    if (!formGroup) return;
    
    // Adicionar classe de erro
    field.classList.add('error');
    formGroup.classList.add('has-error');
    
    // Criar ou atualizar mensagem de erro
    let errorElement = formGroup.querySelector('.error-message');
    if (!errorElement) {
        errorElement = document.createElement('div');
        errorElement.className = 'error-message';
        formGroup.appendChild(errorElement);
    }
    
    errorElement.textContent = message;
    errorElement.style.display = 'block';
    
    // Focar no campo com erro
    field.focus();
}

function clearFieldError(field) {
    const formGroup = field.closest('.form-group');
    if (!formGroup) return;
    
    field.classList.remove('error');
    formGroup.classList.remove('has-error');
    
    const errorElement = formGroup.querySelector('.error-message');
    if (errorElement) {
        errorElement.style.display = 'none';
    }
}

function isValidEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

function isValidPhone(phone) {
    // Aceita vários formatos de telefone
    const re = /^[\d\s\(\)\-\+]+$/;
    return re.test(phone) && phone.replace(/\D/g, '').length >= 10;
}

function isValidURL(url) {
    try {
        new URL(url);
        return true;
    } catch (_) {
        return false;
    }
}

/**
 * Botão "Voltar ao Topo"
 */
function initBackToTop() {
    const backToTopBtn = document.createElement('button');
    backToTopBtn.id = 'back-to-top';
    backToTopBtn.innerHTML = '<i class="fas fa-chevron-up"></i>';
    backToTopBtn.title = 'Voltar ao topo';
    document.body.appendChild(backToTopBtn);
    
    // Mostrar/ocultar botão
    window.addEventListener('scroll', function() {
        if (window.pageYOffset > 300) {
            backToTopBtn.classList.add('show');
        } else {
            backToTopBtn.classList.remove('show');
        }
    });
    
    // Scroll suave ao topo
    backToTopBtn.addEventListener('click', function() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
}

/**
 * Lightbox para Galerias de Imagens
 */
function initGalleryLightbox() {
    // Criar estrutura do lightbox
    const lightboxHTML = `
        <div id="lightbox" class="lightbox">
            <span class="lightbox-close">&times;</span>
            <span class="lightbox-prev"><i class="fas fa-chevron-left"></i></span>
            <span class="lightbox-next"><i class="fas fa-chevron-right"></i></span>
            <div class="lightbox-content">
                <img id="lightbox-img" src="" alt="">
                <div id="lightbox-caption"></div>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', lightboxHTML);
    
    const lightbox = document.getElementById('lightbox');
    const lightboxImg = document.getElementById('lightbox-img');
    const lightboxCaption = document.getElementById('lightbox-caption');
    const closeBtn = lightbox.querySelector('.lightbox-close');
    const prevBtn = lightbox.querySelector('.lightbox-prev');
    const nextBtn = lightbox.querySelector('.lightbox-next');
    
    let currentImages = [];
    let currentIndex = 0;
    
    // Abrir lightbox
    document.addEventListener('click', function(e) {
        const target = e.target;
        
        if (target.classList.contains('gallery-image') || 
            target.parentElement.classList.contains('gallery-image')) {
            e.preventDefault();
            
            const img = target.tagName === 'IMG' ? target : target.querySelector('img');
            const gallery = target.closest('.gallery');
            
            if (gallery) {
                currentImages = Array.from(gallery.querySelectorAll('img'));
                currentIndex = currentImages.indexOf(img);
            } else {
                currentImages = [img];
                currentIndex = 0;
            }
            
            openLightbox(currentImages[currentIndex]);
        }
    });
    
    function openLightbox(imgElement) {
        lightboxImg.src = imgElement.src;
        lightboxCaption.textContent = imgElement.alt || '';
        lightbox.classList.add('active');
        document.body.style.overflow = 'hidden';
        
        // Navegação por teclado
        document.addEventListener('keydown', handleKeyDown);
    }
    
    function closeLightbox() {
        lightbox.classList.remove('active');
        document.body.style.overflow = '';
        document.removeEventListener('keydown', handleKeyDown);
    }
    
    function showNext() {
        currentIndex = (currentIndex + 1) % currentImages.length;
        lightboxImg.src = currentImages[currentIndex].src;
        lightboxCaption.textContent = currentImages[currentIndex].alt || '';
    }
    
    function showPrev() {
        currentIndex = (currentIndex - 1 + currentImages.length) % currentImages.length;
        lightboxImg.src = currentImages[currentIndex].src;
        lightboxCaption.textContent = currentImages[currentIndex].alt || '';
    }
    
    function handleKeyDown(e) {
        switch(e.key) {
            case 'Escape':
                closeLightbox();
                break;
            case 'ArrowRight':
                showNext();
                break;
            case 'ArrowLeft':
                showPrev();
                break;
        }
    }
    
    // Event listeners
    closeBtn.addEventListener('click', closeLightbox);
    prevBtn.addEventListener('click', showPrev);
    nextBtn.addEventListener('click', showNext);
    
    // Fechar ao clicar fora da imagem
    lightbox.addEventListener('click', function(e) {
        if (e.target === lightbox || e.target.classList.contains('lightbox')) {
            closeLightbox();
        }
    });
}

/**
 * Formulário de Contato com AJAX
 */
function initContactForm() {
    const contactForm = document.getElementById('form-contato');
    
    if (!contactForm) return;
    
    // Verificar se reCAPTCHA está configurado
    const recaptchaElement = contactForm.querySelector('.g-recaptcha');
    const hasRecaptcha = recaptchaElement !== null;
    
    contactForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        // Validar formulário
        if (!validateForm(this)) {
            return;
        }
        
        // Validar reCAPTCHA se existir
        if (hasRecaptcha && typeof grecaptcha !== 'undefined') {
            const recaptchaResponse = grecaptcha.getResponse();
            if (!recaptchaResponse) {
                alert('Por favor, complete a verificação do reCAPTCHA.');
                return;
            }
        }
        
        // Desabilitar botão de envio
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.textContent;
        submitBtn.disabled = true;
        submitBtn.textContent = 'Enviando...';
        
        try {
            const formData = new FormData(this);
            
            // Adicionar dados adicionais
            formData.append('ajax', 'true');
            
            const response = await fetch('enviar_contato.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                // Sucesso
                showMessage('success', 'Mensagem enviada com sucesso! Em breve entraremos em contato.');
                contactForm.reset();
                
                // Resetar reCAPTCHA
                if (hasRecaptcha && typeof grecaptcha !== 'undefined') {
                    grecaptcha.reset();
                }
            } else {
                // Erro
                showMessage('error', data.message || 'Erro ao enviar mensagem. Tente novamente.');
            }
        } catch (error) {
            console.error('Erro:', error);
            showMessage('error', 'Erro de conexão. Verifique sua internet e tente novamente.');
        } finally {
            // Reabilitar botão
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
        }
    });
    
    function showMessage(type, text) {
        // Remover mensagens anteriores
        const existingMessages = contactForm.querySelectorAll('.form-message');
        existingMessages.forEach(msg => msg.remove());
        
        // Criar nova mensagem
        const messageDiv = document.createElement('div');
        messageDiv.className = `form-message ${type}`;
        messageDiv.textContent = text;
        
        // Inserir antes do botão de submit
        const submitBtn = contactForm.querySelector('button[type="submit"]');
        contactForm.insertBefore(messageDiv, submitBtn);
        
        // Remover após 5 segundos
        setTimeout(() => {
            messageDiv.remove();
        }, 5000);
    }
}

/**
 * Header Fixo com Efeito Sticky
 */
function initStickyHeader() {
    const header = document.querySelector('.site-header');
    if (!header) return;
    
    let lastScrollTop = 0;
    const headerHeight = header.offsetHeight;
    
    window.addEventListener('scroll', function() {
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        
        // Efeito de mostrar/esconder no scroll
        if (scrollTop > lastScrollTop && scrollTop > headerHeight) {
            // Scroll para baixo
            header.style.transform = 'translateY(-100%)';
        } else {
            // Scroll para cima
            header.style.transform = 'translateY(0)';
        }
        
        // Adicionar sombra quando não estiver no topo
        if (scrollTop > 10) {
            header.classList.add('scrolled');
        } else {
            header.classList.remove('scrolled');
        }
        
        lastScrollTop = scrollTop;
    });
    
    // Ajustar padding-top do body para compensar header fixo
    document.body.style.paddingTop = headerHeight + 'px';
}

/**
 * Animações ao Scroll
 */
function initAnimations() {
    // Verificar se o Intersection Observer está disponível
    if ('IntersectionObserver' in window) {
        const observerOptions = {
            root: null,
            rootMargin: '0px',
            threshold: 0.1
        };
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-in');
                    observer.unobserve(entry.target);
                }
            });
        }, observerOptions);
        
        // Observar elementos com classe .animate-on-scroll
        document.querySelectorAll('.animate-on-scroll').forEach(element => {
            observer.observe(element);
        });
    } else {
        // Fallback para browsers antigos
        document.querySelectorAll('.animate-on-scroll').forEach(element => {
            element.classList.add('animate-in');
        });
    }
}

/**
 * Dropdowns
 */
function initDropdowns() {
    document.querySelectorAll('.dropdown-toggle').forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const dropdown = this.closest('.dropdown');
            dropdown.classList.toggle('open');
            
            // Fechar outros dropdowns
            document.querySelectorAll('.dropdown').forEach(other => {
                if (other !== dropdown) {
                    other.classList.remove('open');
                }
            });
        });
    });
    
    // Fechar dropdowns ao clicar fora
    document.addEventListener('click', function() {
        document.querySelectorAll('.dropdown').forEach(dropdown => {
            dropdown.classList.remove('open');
        });
    });
    
    // Fechar dropdowns ao pressionar ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.dropdown').forEach(dropdown => {
                dropdown.classList.remove('open');
            });
        }
    });
}

/**
 * Modais
 */
function initModals() {
    // Abrir modal
    document.addEventListener('click', function(e) {
        const openBtn = e.target.closest('[data-modal]');
        if (openBtn) {
            const modalId = openBtn.getAttribute('data-modal');
            const modal = document.getElementById(modalId);
            if (modal) {
                openModal(modal);
            }
        }
    });
    
    // Fechar modal
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('modal') || 
            e.target.classList.contains('modal-close') ||
            e.target.closest('.modal-close')) {
            closeModal(e.target.closest('.modal'));
        }
    });
    
    // Fechar modal com ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const openModal = document.querySelector('.modal.open');
            if (openModal) {
                closeModal(openModal);
            }
        }
    });
}

function openModal(modal) {
    modal.classList.add('open');
    document.body.style.overflow = 'hidden';
    
    // Focar no primeiro elemento interativo
    const focusable = modal.querySelector('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])');
    if (focusable) {
        focusable.focus();
    }
}

function closeModal(modal) {
    modal.classList.remove('open');
    document.body.style.overflow = '';
}

/**
 * ============================================
 * FUNÇÕES DA ÁREA ADMINISTRATIVA
 * ============================================
 */

function initAdminComponents() {
    initAdminTables();
    initAdminForms();
    initAdminModals();
    initAdminFileUploads();
    initAdminConfirmations();
    initAdminSearch();
}

/**
 * Tabelas Administrativas com Ordenação
 */
function initAdminTables() {
    document.querySelectorAll('.data-table th.sortable').forEach(th => {
        th.addEventListener('click', function() {
            const table = this.closest('table');
            const columnIndex = Array.from(this.parentNode.children).indexOf(this);
            const isAscending = this.classList.contains('asc');
            
            // Remover classes de ordenação de todas as colunas
            table.querySelectorAll('th').forEach(header => {
                header.classList.remove('asc', 'desc');
            });
            
            // Adicionar classe à coluna atual
            this.classList.add(isAscending ? 'desc' : 'asc');
            
            // Ordenar tabela
            sortTable(table, columnIndex, !isAscending);
        });
    });
}

function sortTable(table, column, ascending = true) {
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));
    
    rows.sort((a, b) => {
        const aVal = a.children[column].textContent.trim();
        const bVal = b.children[column].textContent.trim();
        
        // Tentar converter para número
        const aNum = parseFloat(aVal.replace(/[^\d.-]/g, ''));
        const bNum = parseFloat(bVal.replace(/[^\d.-]/g, ''));
        
        if (!isNaN(aNum) && !isNaN(bNum)) {
            return ascending ? aNum - bNum : bNum - aNum;
        }
        
        // Comparação de strings
        return ascending ? 
            aVal.localeCompare(bVal) : 
            bVal.localeCompare(aVal);
    });
    
    // Reinserir linhas ordenadas
    rows.forEach(row => tbody.appendChild(row));
}

/**
 * Formulários Administrativos
 */
function initAdminForms() {
    // Preview de imagem antes do upload
    document.querySelectorAll('input[type="file"][data-preview]').forEach(input => {
        input.addEventListener('change', function(e) {
            const previewId = this.getAttribute('data-preview');
            const preview = document.getElementById(previewId);
            
            if (!preview) return;
            
            const file = this.files[0];
            if (file && file.type.startsWith('image/')) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    preview.innerHTML = `<img src="${e.target.result}" alt="Preview" style="max-width: 200px;">`;
                };
                
                reader.readAsDataURL(file);
            } else {
                preview.innerHTML = '<p>Arquivo não é uma imagem válida.</p>';
            }
        });
    });
    
    // Máscaras de entrada
    document.querySelectorAll('[data-mask]').forEach(input => {
        const mask = input.getAttribute('data-mask');
        
        input.addEventListener('input', function(e) {
            let value = this.value.replace(/\D/g, '');
            
            switch(mask) {
                case 'phone':
                    value = value.replace(/^(\d{2})(\d)/g, '($1) $2');
                    value = value.replace(/(\d)(\d{4})$/, '$1-$2');
                    break;
                    
                case 'cpf':
                    value = value.replace(/(\d{3})(\d)/, '$1.$2');
                    value = value.replace(/(\d{3})(\d)/, '$1.$2');
                    value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
                    break;
                    
                case 'cep':
                    value = value.replace(/^(\d{5})(\d)/, '$1-$2');
                    break;
                    
                case 'date':
                    value = value.replace(/^(\d{2})(\d)/, '$1/$2');
                    value = value.replace(/^(\d{2}\/\d{2})(\d)/, '$1/$2');
                    break;
            }
            
            this.value = value;
        });
    });
}

/**
 * Modais Administrativos
 */
function initAdminModals() {
    // Modal de confirmação genérica
    window.showConfirmModal = function(options) {
        return new Promise((resolve) => {
            const modalId = 'confirm-modal-' + Date.now();
            const modalHTML = `
                <div id="${modalId}" class="modal admin-modal">
                    <div class="modal-content">
                        <h3>${options.title || 'Confirmar ação'}</h3>
                        <p>${options.message || 'Tem certeza que deseja continuar?'}</p>
                        <div class="modal-actions">
                            <button class="btn btn-secondary cancel-btn">Cancelar</button>
                            <button class="btn btn-danger confirm-btn">${options.confirmText || 'Confirmar'}</button>
                        </div>
                    </div>
                </div>
            `;
            
            document.body.insertAdjacentHTML('beforeend', modalHTML);
            const modal = document.getElementById(modalId);
            
            modal.querySelector('.confirm-btn').addEventListener('click', () => {
                modal.remove();
                resolve(true);
            });
            
            modal.querySelector('.cancel-btn').addEventListener('click', () => {
                modal.remove();
                resolve(false);
            });
            
            // Fechar ao clicar fora
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    modal.remove();
                    resolve(false);
                }
            });
            
            openModal(modal);
        });
    };
}

/**
 * Upload de Arquivos com Progresso
 */
function initAdminFileUploads() {
    document.querySelectorAll('form[data-ajax-upload]').forEach(form => {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;
            const progressBar = this.querySelector('.upload-progress') || 
                               createProgressBar(this);
            
            const formData = new FormData(this);
            const xhr = new XMLHttpRequest();
            
            // Configurar eventos
            xhr.upload.addEventListener('progress', function(e) {
                if (e.lengthComputable) {
                    const percentComplete = (e.loaded / e.total) * 100;
                    progressBar.style.width = percentComplete + '%';
                    progressBar.textContent = Math.round(percentComplete) + '%';
                }
            });
            
            xhr.addEventListener('load', function() {
                try {
                    const response = JSON.parse(xhr.responseText);
                    
                    if (response.success) {
                        showAdminMessage('success', response.message || 'Upload realizado com sucesso!');
                        
                        if (response.redirect) {
                            setTimeout(() => {
                                window.location.href = response.redirect;
                            }, 1500);
                        }
                    } else {
                        showAdminMessage('error', response.message || 'Erro no upload.');
                    }
                } catch (error) {
                    showAdminMessage('error', 'Erro ao processar resposta do servidor.');
                } finally {
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalText;
                    progressBar.style.width = '0%';
                }
            });
            
            xhr.addEventListener('error', function() {
                showAdminMessage('error', 'Erro de conexão durante o upload.');
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
                progressBar.style.width = '0%';
            });
            
            // Desabilitar botão e mostrar progresso
            submitBtn.disabled = true;
            submitBtn.textContent = 'Enviando...';
            progressBar.style.display = 'block';
            
            // Enviar requisição
            xhr.open('POST', this.action);
            xhr.send(formData);
        });
    });
    
    function createProgressBar(form) {
        const progressContainer = document.createElement('div');
        progressContainer.className = 'progress-container';
        
        const progressBar = document.createElement('div');
        progressBar.className = 'upload-progress';
        progressBar.style.display = 'none';
        
        progressContainer.appendChild(progressBar);
        form.appendChild(progressContainer);
        
        return progressBar;
    }
}

/**
 * Confirmações de Ações Críticas
 */
function initAdminConfirmations() {
    document.addEventListener('click', async function(e) {
        const confirmBtn = e.target.closest('[data-confirm]');
        
        if (confirmBtn) {
            e.preventDefault();
            
            const message = confirmBtn.getAttribute('data-confirm') || 
                           'Tem certeza que deseja realizar esta ação?';
            
            const confirmed = await window.showConfirmModal({
                title: 'Confirmar Ação',
                message: message,
                confirmText: confirmBtn.getAttribute('data-confirm-text') || 'Confirmar'
            });
            
            if (confirmed) {
                // Se for um formulário, submeter
                if (confirmBtn.tagName === 'BUTTON' && confirmBtn.form) {
                    confirmBtn.form.submit();
                } 
                // Se for um link, seguir
                else if (confirmBtn.tagName === 'A') {
                    window.location.href = confirmBtn.href;
                }
            }
        }
    });
}

/**
 * Busca na Área Administrativa
 */
function initAdminSearch() {
    const searchInput = document.querySelector('.admin-search input');
    const searchTable = document.querySelector('.data-table');
    
    if (!searchInput || !searchTable) return;
    
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase().trim();
        const rows = searchTable.querySelectorAll('tbody tr');
        
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(searchTerm) ? '' : 'none';
        });
    });
}

/**
 * Mensagens Administrativas
 */
function showAdminMessage(type, text) {
    const messageDiv = document.createElement('div');
    messageDiv.className = `admin-message ${type}`;
    messageDiv.textContent = text;
    
    const container = document.querySelector('.admin-content') || document.body;
    container.insertBefore(messageDiv, container.firstChild);
    
    // Remover após 5 segundos
    setTimeout(() => {
        messageDiv.remove();
    }, 5000);
}

/**
 * ============================================
 * FUNÇÕES UTILITÁRIAS
 * ============================================
 */

// Debounce para eventos de resize e scroll
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Throttle para eventos frequentes
function throttle(func, limit) {
    let inThrottle;
    return function() {
        const args = arguments;
        const context = this;
        if (!inThrottle) {
            func.apply(context, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    };
}

// Formatar datas
function formatDate(date, format = 'pt-BR') {
    const d = new Date(date);
    return d.toLocaleDateString(format);
}

// Copiar para clipboard
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        showAdminMessage('success', 'Copiado para a área de transferência!');
    }).catch(err => {
        console.error('Erro ao copiar:', err);
        showAdminMessage('error', 'Erro ao copiar texto.');
    });
}

// Verificar se elemento está visível na viewport
function isElementInViewport(el) {
    const rect = el.getBoundingClientRect();
    return (
        rect.top >= 0 &&
        rect.left >= 0 &&
        rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
        rect.right <= (window.innerWidth || document.documentElement.clientWidth)
    );
}

// Obter parâmetros da URL
function getUrlParam(param) {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get(param);
}

// Exportar funções globais (se necessário)
window.copyToClipboard = copyToClipboard;
window.formatDate = formatDate;
window.getUrlParam = getUrlParam;

// Inicialização final quando tudo estiver carregado
window.addEventListener('load', function() {
    console.log('Página completamente carregada');
    
    // Remover classe de loading se existir
    document.body.classList.remove('loading');
    
    // Atualizar ano do copyright
    const copyrightElements = document.querySelectorAll('.copyright-year');
    copyrightElements.forEach(el => {
        el.textContent = new Date().getFullYear();
    });
});