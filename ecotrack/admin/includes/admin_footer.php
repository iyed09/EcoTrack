                    </div>
                    <!-- / Content -->

                    <!-- Footer -->
                    <footer class="content-footer footer bg-footer-theme">
                        <div class="container-xxl d-flex flex-wrap justify-content-between py-2 flex-md-row flex-column">
                            <div class="mb-2 mb-md-0">
                                &copy; <?php echo date('Y'); ?> <a href="<?php echo ROOT_PATH; ?>/index.php" class="footer-link fw-bolder">EcoTrack</a> - Promoting Sustainable Living
                            </div>
                            <div>
                                <a href="<?php echo ROOT_PATH; ?>/index.php" class="footer-link me-4">Home</a>
                                <a href="<?php echo ROOT_PATH; ?>/dashboard.php" class="footer-link me-4">Dashboard</a>
                                <a href="#" class="footer-link">Support</a>
                            </div>
                        </div>
                    </footer>
                    <!-- / Footer -->

                    <div class="content-backdrop fade"></div>
                </div>
                <!-- Content wrapper -->
            </div>
            <!-- / Layout page -->
        </div>

        <!-- Overlay -->
        <div class="layout-overlay layout-menu-toggle"></div>
    </div>
    <!-- / Layout wrapper -->

    <!-- Core JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Main JS -->
    <script src="<?php echo ROOT_PATH; ?>/admin/assets/js/sneat-main.js"></script>
    
    <!-- Global Search JS -->
    <script>
    (function() {
        const searchInput = document.getElementById('globalSearch');
        const searchResults = document.getElementById('searchResults');
        let searchTimeout = null;
        
        if (!searchInput || !searchResults) return;
        
        searchInput.addEventListener('input', function(e) {
            const query = e.target.value.trim();
            
            if (searchTimeout) clearTimeout(searchTimeout);
            
            if (query.length < 2) {
                searchResults.style.display = 'none';
                return;
            }
            
            searchTimeout = setTimeout(function() {
                fetch('<?php echo ROOT_PATH; ?>/admin/api/search.php?q=' + encodeURIComponent(query))
                    .then(response => response.json())
                    .then(data => {
                        if (data.results && data.results.length > 0) {
                            let html = '<div class="list-group list-group-flush">';
                            data.results.forEach(function(item) {
                                const typeColors = {
                                    'user': 'bg-label-primary',
                                    'report': 'bg-label-warning',
                                    'page': 'bg-label-info'
                                };
                                const colorClass = typeColors[item.type] || 'bg-label-secondary';
                                
                                html += '<a href="' + item.url + '" class="list-group-item list-group-item-action d-flex align-items-center py-2">';
                                html += '<span class="avatar avatar-sm me-3"><span class="avatar-initial rounded ' + colorClass + '"><i class="bx ' + item.icon + '"></i></span></span>';
                                html += '<div class="flex-grow-1">';
                                html += '<div class="fw-medium">' + item.title + '</div>';
                                html += '<small class="text-muted">' + item.subtitle + '</small>';
                                html += '</div>';
                                html += '<span class="badge ' + colorClass + ' text-capitalize">' + item.type + '</span>';
                                html += '</a>';
                            });
                            html += '</div>';
                            searchResults.innerHTML = html;
                            searchResults.style.display = 'block';
                        } else {
                            searchResults.innerHTML = '<div class="p-3 text-center text-muted"><i class="bx bx-search-alt fs-4 d-block mb-2"></i>No results found</div>';
                            searchResults.style.display = 'block';
                        }
                    })
                    .catch(function(error) {
                        console.error('Search error:', error);
                        searchResults.style.display = 'none';
                    });
            }, 300);
        });
        
        document.addEventListener('click', function(e) {
            if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
                searchResults.style.display = 'none';
            }
        });
        
        searchInput.addEventListener('focus', function() {
            if (this.value.trim().length >= 2 && searchResults.innerHTML.trim() !== '') {
                searchResults.style.display = 'block';
            }
        });
    })();
    </script>
</body>
</html>
