        </main>
    </div>
    
    <!-- Scripts -->
    <script src="../js/scripts.js"></script>
    
    <!-- Scripts específicos da área admin -->
    <script>
    // Ativar tooltips
    document.querySelectorAll('[title]').forEach(element => {
        element.addEventListener('mouseenter', function(e) {
            const title = this.getAttribute('title');
            if (title) {
                const tooltip = document.createElement('div');
                tooltip.className = 'tooltip';
                tooltip.textContent = title;
                tooltip.style.cssText = `
                    position: fixed;
                    background: #333;
                    color: white;
                    padding: 5px 10px;
                    border-radius: 4px;
                    font-size: 0.9rem;
                    z-index: 99999;
                    pointer-events: none;
                    transform: translate(-50%, -100%);
                    margin-top: -10px;
                `;
                document.body.appendChild(tooltip);
                
                const rect = this.getBoundingClientRect();
                tooltip.style.left = rect.left + (rect.width / 2) + 'px';
                tooltip.style.top = rect.top + 'px';
                
                this._tooltip = tooltip;
                this.removeAttribute('title');
            }
        });
        
        element.addEventListener('mouseleave', function() {
            if (this._tooltip) {
                this._tooltip.remove();
                this.setAttribute('title', this._tooltip.textContent);
                delete this._tooltip;
            }
        });
    });
    
    // Confirmar ações críticas
    document.querySelectorAll('form[data-confirm]').forEach(form => {
        form.addEventListener('submit', function(e) {
            const message = this.getAttribute('data-confirm');
            if (message && !confirm(message)) {
                e.preventDefault();
            }
        });
    });
    
    // Auto-save para formulários longos
    const autoSaveForms = document.querySelectorAll('form[data-autosave]');
    autoSaveForms.forEach(form => {
        let saveTimeout;
        
        form.addEventListener('input', function() {
            clearTimeout(saveTimeout);
            saveTimeout = setTimeout(() => {
                // Implementar auto-save via AJAX aqui
                console.log('Auto-save para:', this.id || 'formulário sem ID');
            }, 2000);
        });
    });
    
    // Inicializar componentes específicos
    document.addEventListener('DOMContentLoaded', function() {
        // Ordenação de tabelas
        document.querySelectorAll('.data-table th.sortable').forEach(th => {
            th.addEventListener('click', function() {
                const table = this.closest('table');
                const columnIndex = Array.from(this.parentNode.children).indexOf(this);
                const isAscending = this.classList.contains('asc');
                
                // Remover classes de ordenação
                table.querySelectorAll('th').forEach(header => {
                    header.classList.remove('asc', 'desc');
                });
                
                // Adicionar classe à coluna atual
                this.classList.add(isAscending ? 'desc' : 'asc');
                
                // Ordenar tabela
                sortTable(table, columnIndex, !isAscending);
            });
        });
        
        // Busca em tempo real
        const searchInput = document.querySelector('.admin-search input');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                const table = document.querySelector('.data-table');
                
                if (table) {
                    const rows = table.querySelectorAll('tbody tr');
                    rows.forEach(row => {
                        const text = row.textContent.toLowerCase();
                        row.style.display = text.includes(searchTerm) ? '' : 'none';
                    });
                }
            });
        }
        
        // Atualizar contador de caracteres
        document.querySelectorAll('textarea[maxlength]').forEach(textarea => {
            const maxLength = textarea.getAttribute('maxlength');
            const counter = document.createElement('div');
            counter.className = 'char-counter';
            counter.style.cssText = 'text-align: right; font-size: 0.8rem; color: #666; margin-top: 5px;';
            textarea.parentNode.appendChild(counter);
            
            function updateCounter() {
                const currentLength = textarea.value.length;
                counter.textContent = `${currentLength}/${maxLength} caracteres`;
                
                if (currentLength > maxLength * 0.9) {
                    counter.style.color = '#e74c3c';
                } else if (currentLength > maxLength * 0.7) {
                    counter.style.color = '#f39c12';
                } else {
                    counter.style.color = '#666';
                }
            }
            
            textarea.addEventListener('input', updateCounter);
            updateCounter();
        });
    });
    
    // Função para ordenar tabela
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
            
            // Comparação de datas
            const aDate = Date.parse(aVal);
            const bDate = Date.parse(bVal);
            
            if (!isNaN(aDate) && !isNaN(bDate)) {
                return ascending ? aDate - bDate : bDate - aDate;
            }
            
            // Comparação de strings
            return ascending ? 
                aVal.localeCompare(bVal, 'pt-BR', { sensitivity: 'base' }) : 
                bVal.localeCompare(aVal, 'pt-BR', { sensitivity: 'base' });
        });
        
        // Reinserir linhas ordenadas
        rows.forEach(row => tbody.appendChild(row));
    }
    
    // Função para exportar dados
    window.exportToCSV = function(tableId, filename = 'export.csv') {
        const table = document.getElementById(tableId);
        if (!table) return;
        
        const rows = table.querySelectorAll('tr');
        const csv = [];
        
        rows.forEach(row => {
            const rowData = [];
            row.querySelectorAll('th, td').forEach(cell => {
                // Remover botões e ações
                if (!cell.closest('td:last-child')) {
                    rowData.push(`"${cell.textContent.replace(/"/g, '""')}"`);
                }
            });
            csv.push(rowData.join(','));
        });
        
        const csvContent = csv.join('\n');
        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        
        if (navigator.msSaveBlob) {
            navigator.msSaveBlob(blob, filename);
        } else {
            link.href = URL.createObjectURL(blob);
            link.download = filename;
            link.style.display = 'none';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
    };
    </script>
    
    <!-- Notificações em tempo real (opcional) -->
    <script>
    // Verificar novos contatos a cada 30 segundos
    setInterval(() => {
        fetch('check_novos_contatos.php')
            .then(response => response.json())
            .then(data => {
                if (data.novos > 0) {
                    // Mostrar notificação
                    if (Notification.permission === 'granted') {
                        new Notification('Novos contatos!', {
                            body: `Você tem ${data.novos} novo(s) contato(s)`,
                            icon: '../uploads/logos/logo.png'
                        });
                    } else if (Notification.permission !== 'denied') {
                        Notification.requestPermission();
                    }
                }
            });
    }, 30000);
    </script>
</body>
</html>