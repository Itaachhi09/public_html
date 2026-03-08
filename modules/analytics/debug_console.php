<?php
/**
 * Analytics Debug Console
 * Shows real-time API responses and errors
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['token'])) {
    die('Unauthorized');
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics Debug Console</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Monaco', 'Menlo', monospace; 
            background: #1e1e1e; 
            color: #d4d4d4;
            padding: 20px;
        }
        .container { max-width: 1200px; margin: 0 auto; }
        h1 { color: #4ec9b0; margin-bottom: 20px; }
        .controls {
            background: #252526;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            border: 1px solid #3e3e42;
        }
        .control-group {
            display: inline-block;
            margin-right: 15px;
            margin-bottom: 10px;
        }
        label { 
            display: block; 
            font-size: 12px; 
            color: #858585;
            margin-bottom: 5px;
        }
        select, input {
            background: #3c3c3c;
            color: #d4d4d4;
            border: 1px solid #3e3e42;
            padding: 6px 8px;
            border-radius: 3px;
            font-family: inherit;
        }
        button {
            background: #007acc;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 3px;
            cursor: pointer;
            font-family: inherit;
        }
        button:hover { background: #005a9e; }
        .output {
            background: #1e1e1e;
            border: 1px solid #3e3e42;
            border-radius: 4px;
            padding: 15px;
            min-height: 300px;
            max-height: 600px;
            overflow-y: auto;
            font-size: 12px;
        }
        .success { color: #4ec9b0; }
        .error { color: #f48771; }
        .info { color: #9cdcfe; }
        .warning { color: #dcdcaa; }
        .line { margin-bottom: 8px; }
        .json { color: #b4cea8; }
        .key { color: #9cdcfe; }
        .string { color: #ce9178; }
        .number { color: #b5cea8; }
        .boolean { color: #569cd6; }
        .null { color: #569cd6; }
        .endpoint-button {
            display: inline-block;
            background: #3c3c3c;
            border: 1px solid #3e3e42;
            padding: 8px 12px;
            margin: 5px;
            border-radius: 3px;
            cursor: pointer;
            font-family: inherit;
            color: #d4d4d4;
            font-size: 11px;
        }
        .endpoint-button:hover {
            background: #3e3e42;
        }
        .endpoints {
            background: #252526;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            border: 1px solid #3e3e42;
        }
        .endpoints h3 { color: #4ec9b0; margin-bottom: 10px; font-size: 14px; }
        .clear-btn {
            background: #d16969;
            margin-left: 10px;
        }
        .clear-btn:hover {
            background: #aa4d4d;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>🔧 Analytics Debug Console</h1>
    
    <div class="controls">
        <div class="control-group">
            <label>Action:</label>
            <select id="action">
                <option value="">-- Select Action --</option>
                <option value="getDashboardData">getDashboardData</option>
                <option value="getMetrics">getMetrics</option>
                <option value="getHeadcountAnalytics">getHeadcountAnalytics</option>
                <option value="getPayrollTrends">getPayrollTrends</option>
                <option value="getCompensationAnalysis">getCompensationAnalysis</option>
                <option value="getHMOInsights">getHMOInsights</option>
                <option value="getComplianceTracking">getComplianceTracking</option>
                <option value="getMovementAnalytics">getMovementAnalytics</option>
                <option value="getCostAnalysis">getCostAnalysis</option>
            </select>
        </div>

        <div class="control-group">
            <label>Date Range (days):</label>
            <input type="number" id="dateRange" value="30" min="1" max="365">
        </div>

        <div class="control-group">
            <label>Department ID:</label>
            <input type="number" id="department" placeholder="Leave empty for all">
        </div>

        <button onclick="executeQuery()">Execute Query</button>
        <button onclick="document.querySelector('.output').innerHTML = ''" class="clear-btn">Clear Output</button>
    </div>

    <div class="endpoints">
        <h3>Quick Actions:</h3>
        <div class="endpoint-button" onclick="testEndpoint('getDashboardData')">Dashboard Data</div>
        <div class="endpoint-button" onclick="testEndpoint('getMetrics')">Get Metrics</div>
        <div class="endpoint-button" onclick="testEndpoint('getHeadcountAnalytics')">Headcount Analytics</div>
        <div class="endpoint-button" onclick="testEndpoint('getPayrollTrends')">Payroll Trends</div>
        <div class="endpoint-button" onclick="testEndpoint('getHMOInsights')">HMO Insights</div>
        <div class="endpoint-button" onclick="testEndpoint('getCostAnalysis')">Cost Analysis</div>
        <div class="endpoint-button" onclick="testDatabaseConnection()">Test DB Connection</div>
    </div>

    <div class="output" id="output">
        <div class="line info">Ready. Select an action and click "Execute Query" to test API endpoints.</div>
    </div>
</div>

<script>
const output = document.getElementById('output');

function addLog(message, type = 'info') {
    const div = document.createElement('div');
    div.className = `line ${type}`;
    div.textContent = `[${new Date().toLocaleTimeString()}] ${message}`;
    output.appendChild(div);
    output.scrollTop = output.scrollHeight;
}

function addJSON(data, type = 'json') {
    const div = document.createElement('div');
    div.className = `line ${type}`;
    div.textContent = JSON.stringify(data, null, 2);
    div.style.whiteSpace = 'pre-wrap';
    div.style.wordBreak = 'break-all';
    output.appendChild(div);
    output.scrollTop = output.scrollHeight;
}

function testEndpoint(action) {
    document.getElementById('action').value = action;
    executeQuery();
}

async function executeQuery() {
    const action = document.getElementById('action').value;
    
    if (!action) {
        addLog('Please select an action', 'warning');
        return;
    }

    const dateRange = document.getElementById('dateRange').value;
    const department = document.getElementById('department').value;
    
    let url = `/modules/analytics/api.php?action=${action}&dateRange=${dateRange}`;
    if (department) {
        url += `&department=${department}`;
    }

    addLog(`Requesting: ${url}`, 'info');
    addLog(`─────────────────────────────────────────`, 'info');

    try {
        const response = await fetch(url, {
            method: 'GET',
            headers: {
                'Accept': 'application/json'
            },
            credentials: 'same-origin'
        });

        addLog(`Response Status: ${response.status} ${response.statusText}`, 
            response.ok ? 'success' : 'error');

        const data = await response.json();
        
        if (response.ok) {
            addLog(`Success: Data received`, 'success');
            addLog(`Total keys: ${Object.keys(data.data || {}).length}`, 'info');
            addJSON(data, 'json');
        } else {
            addLog(`Error: ${data.error || 'Unknown error'}`, 'error');
            addJSON(data, 'error');
        }
    } catch (error) {
        addLog(`Network Error: ${error.message}`, 'error');
        addJSON({error: error.message}, 'error');
    }

    addLog(`─────────────────────────────────────────`, 'info');
}

async function testDatabaseConnection() {
    addLog(`Testing database connection...`, 'info');
    addLog(`─────────────────────────────────────────`, 'info');

    try {
        const response = await fetch('/modules/analytics/test_connection.php', {
            credentials: 'same-origin'
        });
        
        if (response.ok) {
            addLog(`Connection Test Page: ${response.status} (Open in new tab to see results)`, 'success');
            addLog(`URL: /modules/analytics/test_connection.php`, 'info');
        } else {
            addLog(`Error: ${response.status}`, 'error');
        }
    } catch (error) {
        addLog(`Network Error: ${error.message}`, 'error');
    }
}

// Test initial connection
window.addEventListener('load', () => {
    addLog('Analytics Debug Console loaded', 'success');
    addLog('Ready to test API endpoints', 'info');
});
</script>

</body>
</html>
