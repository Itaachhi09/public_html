<?php
/**
 * Analytics Promotion Candidates View
 * Displays AI-predicted promotion candidates
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['token'])) {
    header('Location: ../../../index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Promotion Candidates</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --dark: #1a1a2e;
            --light: #f4f6f9;
            --primary: #0066cc;
            --success: #28a745;
            --background: #f9f9f9;
            --border: #e0e0e0;
            --text-dark: #333;
            --text-light: #666;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: var(--light);
        }

        .promotion-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
        }

        .header {
            margin-bottom: 2.5rem;
        }

        .header h1 {
            font-size: 2rem;
            margin-bottom: 0.5rem;
            color: var(--dark);
        }

        .header p {
            color: var(--text-light);
            font-size: 0.95rem;
        }

        .controls {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }

        .control-group {
            display: flex;
            flex-direction: column;
            gap: 0.3rem;
        }

        .control-group label {
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .control-group select,
        .btn {
            padding: 0.6rem 1rem;
            border: 1px solid var(--border);
            border-radius: 4px;
            font-size: 0.95rem;
        }

        .btn {
            background: var(--success);
            color: white;
            cursor: pointer;
            border: none;
            font-weight: 600;
            transition: background 0.3s;
        }

        .btn:hover {
            background: #218838;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
            border-top: 3px solid var(--success);
        }

        .stat-label {
            font-size: 0.85rem;
            color: var(--text-light);
            font-weight: 600;
            text-transform: uppercase;
            margin-bottom: 0.5rem;
        }

        .stat-value {
            font-size: 2.2rem;
            font-weight: 700;
            color: var(--dark);
        }

        .candidates-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .candidate-card {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .candidate-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .card-header {
            background: linear-gradient(135deg, var(--primary), #0052a3);
            color: white;
            padding: 1.5rem;
            position: relative;
        }

        .card-header h3 {
            margin: 0 0 0.3rem 0;
            font-size: 1.1rem;
        }

        .card-header p {
            margin: 0;
            font-size: 0.9rem;
            opacity: 0.9;
        }

        .badge-readiness {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: rgba(255,255,255,0.2);
            color: white;
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .card-body {
            padding: 1.5rem;
        }

        .metric {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--border);
        }

        .metric:last-child {
            margin-bottom: 0;
            border-bottom: none;
            padding-bottom: 0;
        }

        .metric-label {
            font-size: 0.85rem;
            color: var(--text-light);
            font-weight: 500;
        }

        .metric-value {
            font-size: 1rem;
            font-weight: 600;
            color: var(--dark);
        }

        .progress-mini {
            width: 100%;
            height: 4px;
            background: var(--background);
            border-radius: 2px;
            margin-top: 0.5rem;
            overflow: hidden;
        }

        .progress-mini-fill {
            height: 100%;
            background: var(--success);
            border-radius: 2px;
        }

        .button-group {
            display: flex;
            gap: 0.5rem;
        }

        .btn-small {
            flex: 1;
            padding: 0.6rem;
            border: 1px solid var(--border);
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.85rem;
            font-weight: 600;
            transition: background 0.2s;
        }

        .btn-small.primary {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        .btn-small.primary:hover {
            background: #0052a3;
        }

        .btn-small.secondary {
            background: var(--light);
            color: var(--text-dark);
        }

        .btn-small.secondary:hover {
            background: #e8e8e8;
        }

        .empty-state {
            text-align: center;
            padding: 3rem 2rem;
            background: white;
            border-radius: 8px;
            color: var(--text-light);
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        .loading {
            text-align: center;
            padding: 2rem;
        }

        .spinner {
            display: inline-block;
            width: 40px;
            height: 40px;
            border: 4px solid var(--border);
            border-top-color: var(--primary);
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="promotion-container">
        <div class="header">
            <h1><i class='bx bxs-star'></i> Promotion Candidates</h1>
            <p>AI-identified employees with high promotion readiness scores</p>
        </div>

        <div class="controls">
            <div class="control-group">
                <label>Department</label>
                <select id="departmentFilter">
                    <option value="">All Departments</option>
                </select>
            </div>
            <button class="btn" onclick="loadCandidates()">
                <i class='bx bx-refresh'></i> Refresh
            </button>
        </div>

        <div class="stats">
            <div class="stat-card">
                <div class="stat-label">Total Candidates</div>
                <div class="stat-value" id="totalCandidates">0</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Avg Readiness Score</div>
                <div class="stat-value" id="avgReadiness">0%</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">High Potential</div>
                <div class="stat-value" id="highPotential">0</div>
            </div>
        </div>

        <div id="candidatesContainer">
            <div class="loading">
                <div class="spinner"></div>
                <p>Loading candidates...</p>
            </div>
        </div>
    </div>

    <script>
        async function loadCandidates() {
            const department = document.getElementById('departmentFilter').value;

            try {
                const response = await fetch('./api.php?action=getPromotionCandidates&department=' + department + '&limit=50', {
                    headers: {
                        'Authorization': 'Bearer ' + (localStorage.getItem('token') || '')
                    }
                });

                if (!response.ok) throw new Error('Failed to fetch candidates');

                const data = await response.json();
                
                if (data.success && data.data) {
                    displayCandidates(data.data);
                } else {
                    document.getElementById('candidatesContainer').innerHTML = 
                        '<div class="empty-state"><p>No candidates found or AI service unavailable</p></div>';
                }
            } catch (error) {
                console.error('Error:', error);
                document.getElementById('candidatesContainer').innerHTML = 
                    '<div class="empty-state"><p>Error loading data: ' + error.message + '</p></div>';
            }
        }

        function displayCandidates(candidates) {
            if (!candidates || candidates.length === 0) {
                document.getElementById('candidatesContainer').innerHTML = 
                    '<div class="empty-state"><i class="bx bx-inbox"></i><p>No promotion candidates found</p></div>';
                return;
            }

            document.getElementById('totalCandidates').textContent = candidates.length;
            const avgReadiness = Math.round(
                candidates.reduce((sum, c) => sum + (c.readiness_score || 0), 0) / candidates.length
            );
            document.getElementById('avgReadiness').textContent = avgReadiness + '%';
            document.getElementById('highPotential').textContent = 
                candidates.filter(c => (c.readiness_score || 0) > 70).length;

            let html = '<div class="candidates-grid">';
            candidates.forEach(candidate => {
                const readiness = candidate.readiness_score || 0;
                const attrition = candidate.attrition_risk || 0;
                
                html += `<div class="candidate-card">
                    <div class="card-header">
                        <h3>${candidate.name}</h3>
                        <p>${candidate.department}</p>
                        <div class="badge-readiness">${readiness}% Ready</div>
                    </div>
                    <div class="card-body">
                        <div class="metric">
                            <span class="metric-label">Promotion Probability</span>
                            <span class="metric-value">${candidate.promotion_probability}%</span>
                        </div>
                        <div class="progress-mini">
                            <div class="progress-mini-fill" style="width: ${candidate.promotion_probability}%"></div>
                        </div>
                        
                        <div class="metric">
                            <span class="metric-label">Attrition Risk</span>
                            <span class="metric-value">${attrition}%</span>
                        </div>
                        <div class="progress-mini">
                            <div class="progress-mini-fill" style="background: #ffc107; width: ${attrition}%"></div>
                        </div>
                        
                        <div class="metric">
                            <span class="metric-label">Tenure (days)</span>
                            <span class="metric-value">${candidate.tenure_days}</span>
                        </div>

                        <div class="button-group">
                            <button class="btn-small primary" onclick="createPromotion(${candidate.employee_id})">
                                <i class='bx bx-upload'></i> Create Recommendation
                            </button>
                            <button class="btn-small secondary" onclick="viewProfile(${candidate.employee_id})">
                                <i class='bx bx-show'></i> View
                            </button>
                        </div>
                    </div>
                </div>`;
            });
            html += '</div>';

            document.getElementById('candidatesContainer').innerHTML = html;
        }

        function createPromotion(employeeId) {
            alert('Promotion recommendation created for employee ' + employeeId);
            // TODO: Open promotion dialog or redirect
        }

        function viewProfile(employeeId) {
            // TODO: Open employee profile
        }

        // Load on page load
        document.addEventListener('DOMContentLoaded', loadCandidates);
    </script>
</body>
</html>
