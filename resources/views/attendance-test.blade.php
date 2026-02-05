<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Test</title>
</head>
<body style="font-family: sans-serif; padding: 2rem; background: #f8fafc;">
    <div style="max-width: 600px; margin: 0 auto; background: white; padding: 2rem; border-radius: 1rem; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
        <h1 style="color: #0f172a; margin-bottom: 1rem;">Attendance System Test</h1>
        <p style="color: #64748b; margin-bottom: 2rem;">If you can see this page, the route is working correctly.</p>
        
        <div style="background: #f1f5f9; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1rem;">
            <h3 style="margin: 0 0 0.5rem 0; color: #0f172a;">React Loading Test:</h3>
            <div id="react-test" style="padding: 1rem; background: white; border-radius: 0.5rem;">
                <p>Waiting for React...</p>
            </div>
        </div>
        
        <div style="background: #fef3c7; padding: 1rem; border-radius: 0.5rem; border-left: 4px solid #f59e0b;">
            <h3 style="margin: 0 0 0.5rem 0; color: #92400e;">Console Errors:</h3>
            <div id="console-errors" style="font-family: monospace; font-size: 0.75rem; color: #dc2626; max-height: 200px; overflow-y: auto;">
                No errors yet...
            </div>
        </div>
        
        <a href="{{ route('car.wash') }}" style="display: inline-block; margin-top: 1rem; padding: 0.75rem 1.5rem; background: #3b82f6; color: white; text-decoration: none; border-radius: 0.5rem; font-weight: 600;">
            ← Back to Car Wash
        </a>
    </div>
    
    <!-- Load React -->
    <script crossorigin src="https://unpkg.com/react@18/umd/react.production.min.js"></script>
    <script crossorigin src="https://unpkg.com/react-dom@18/umd/react-dom.production.min.js"></script>
    
    <script>
        // Capture console errors
        const errorsList = [];
        const originalError = console.error;
        console.error = function(...args) {
            errorsList.push(args.join(' '));
            document.getElementById('console-errors').innerHTML = errorsList.map(e => `<div style="margin-bottom: 0.5rem;">${e}</div>`).join('');
            originalError.apply(console, args);
        };
        
        // Test React
        setTimeout(function() {
            const testDiv = document.getElementById('react-test');
            
            if (typeof React === 'undefined') {
                testDiv.innerHTML = '<p style="color: #dc2626;">❌ React failed to load!</p>';
                return;
            }
            
            if (typeof ReactDOM === 'undefined') {
                testDiv.innerHTML = '<p style="color: #dc2626;">❌ ReactDOM failed to load!</p>';
                return;
            }
            
            try {
                const root = ReactDOM.createRoot(testDiv);
                root.render(React.createElement('div', { style: { color: '#10b981', fontWeight: 'bold' } }, 
                    '✓ React is working! Version: ' + React.version
                ));
            } catch (error) {
                testDiv.innerHTML = '<p style="color: #dc2626;">❌ Error: ' + error.message + '</p>';
                console.error('React render error:', error);
            }
        }, 1000);
    </script>
</body>
</html>
