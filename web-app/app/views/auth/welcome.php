<?php
$title = 'Welcome';
require 'app/views/layouts/header.php';
?>

<div style="max-width: 800px; margin: 4rem auto; text-align: center;">
    <h1>Welcome to SkillOffice</h1>
    <p style="font-size: 1.25rem; color: var(--text-secondary); margin: var(--spacing-xl) 0;">
        Connecting customers with skilled artisans across Africa
    </p>
    
    <div style="margin: var(--spacing-2xl) 0;">
        <a href="<?php echo url('role-selection'); ?>" id="getStartedBtn" class="btn btn-primary" style="font-size: 1.125rem; padding: var(--spacing-md) var(--spacing-xl);">
            Get Started
        </a>
        <div id="connectionStatus" style="margin-top: var(--spacing-md); font-size: 0.875rem;"></div>
    </div>
    
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: var(--spacing-xl); margin-top: var(--spacing-2xl);">
        <div>
            <h3>🔍 Find Artisans</h3>
            <p class="text-secondary">Search by location, skill, and ratings</p>
        </div>
        <div>
            <h3>💼 Grow Business</h3>
            <p class="text-secondary">Artisans can showcase skills and get jobs</p>
        </div>
        <div>
            <h3>🔒 Secure</h3>
            <p class="text-secondary">Safe and trusted platform</p>
        </div>
    </div>
</div>

<script>
(function() {
    const getStartedBtn = document.getElementById('getStartedBtn');
    const connectionStatus = document.getElementById('connectionStatus');
    let connectionChecked = false;
    let connectionSuccessful = false;
    
    // Test Firebase connection on page load
    const startTime = Date.now();
    console.log('[Firebase Connection Test] Starting connection test...');
    console.log('[Firebase Connection Test] Timestamp:', new Date().toISOString());
    connectionStatus.textContent = 'Checking Firebase connection...';
    connectionStatus.style.color = 'var(--text-secondary)';
    
    fetch('<?php echo url('api/test/connection'); ?>')
        .then(response => {
            const elapsed = Date.now() - startTime;
            console.log('[Firebase Connection Test] Response received in', elapsed + 'ms');
            return response.json();
        })
        .then(data => {
            connectionChecked = true;
            const elapsed = Date.now() - startTime;
            
            if (data.success) {
                connectionSuccessful = true;
                console.log('✅ [Firebase Connection Test] Connection successful!');
                console.log('   Project ID:', data.projectId);
                console.log('   HTTP Status Code:', data.httpCode);
                console.log('   Message:', data.message);
                if (data.timestamp) {
                    console.log('   Server Timestamp:', data.timestamp);
                }
                console.log('   Total Time:', elapsed + 'ms');
                connectionStatus.textContent = '✓ Firebase connected';
                connectionStatus.style.color = 'var(--success, #28a745)';
                
                // Enable navigation
                getStartedBtn.style.opacity = '1';
                getStartedBtn.style.cursor = 'pointer';
            } else {
                connectionSuccessful = false;
                console.error('❌ [Firebase Connection Test] Connection failed!');
                console.error('   Error Message:', data.message);
                console.error('   HTTP Status Code:', data.httpCode || 'N/A');
                if (data.error) {
                    console.error('   Error Details:', data.error);
                }
                console.error('   Total Time:', elapsed + 'ms');
                connectionStatus.textContent = '✗ Firebase connection failed: ' + (data.message || 'Unknown error');
                connectionStatus.style.color = 'var(--error, #dc3545)';
                
                // Disable navigation
                getStartedBtn.style.opacity = '0.5';
                getStartedBtn.style.cursor = 'not-allowed';
                getStartedBtn.onclick = function(e) {
                    e.preventDefault();
                    alert('Cannot proceed: Firebase connection failed. Please check your internet connection and try again.');
                    return false;
                };
            }
        })
        .catch(error => {
            connectionChecked = true;
            connectionSuccessful = false;
            const elapsed = Date.now() - startTime;
            console.error('❌ [Firebase Connection Test] Network error:', error);
            console.error('   Error Type:', error.name);
            console.error('   Error Message:', error.message);
            console.error('   Total Time:', elapsed + 'ms');
            connectionStatus.textContent = '✗ Connection test failed: ' + error.message;
            connectionStatus.style.color = 'var(--error, #dc3545)';
            
            // Disable navigation
            getStartedBtn.style.opacity = '0.5';
            getStartedBtn.style.cursor = 'not-allowed';
            getStartedBtn.onclick = function(e) {
                e.preventDefault();
                alert('Cannot proceed: Unable to verify Firebase connection. Please check your internet connection and try again.');
                return false;
            };
        });
    
    // Prevent navigation until connection is checked
    getStartedBtn.addEventListener('click', function(e) {
        if (!connectionChecked) {
            e.preventDefault();
            console.log('⏳ Waiting for connection check to complete...');
            return false;
        }
        
        if (!connectionSuccessful) {
            e.preventDefault();
            alert('Cannot proceed: Firebase connection failed. Please check your internet connection and try again.');
            return false;
        }
    });
})();
</script>

<?php require 'app/views/layouts/footer.php'; ?>

