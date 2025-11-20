<?php
session_start();

// Database Configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "hackcelerate_voting";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle Form Submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'vote':
                handleVote($conn);
                break;
            case 'admin_login':
                handleAdminLogin();
                break;
            case 'admin_logout':
                handleAdminLogout();
                break;
            case 'get_voter_details':
                getVoterDetails($conn);
                break;
            case 'vote_problem':
                handleProblemVote($conn);
                break;
        }
    }
}

function handleVote($conn) {
    $voterName = $conn->real_escape_string($_POST['voterName']);
    $voterEmail = $conn->real_escape_string($_POST['voterEmail']);
    $voterId = $conn->real_escape_string($_POST['voterId']);
    $voterBranch = $conn->real_escape_string($_POST['voterBranch']);
    $voterYear = $conn->real_escape_string($_POST['voterYear']);
    $teamId = $conn->real_escape_string($_POST['teamId']);

    // Check if voter already exists
    $checkVoter = $conn->query("SELECT id FROM voters WHERE email = '$voterEmail' OR student_id = '$voterId'");
    if ($checkVoter->num_rows > 0) {
        echo "<script>alert('You have already voted! Each student can only vote once.');</script>";
        return;
    }

    // Insert voter
    $conn->query("INSERT INTO voters (name, email, student_id, branch, year) VALUES ('$voterName', '$voterEmail', '$voterId', '$voterBranch', '$voterYear')");
    $voterIdInserted = $conn->insert_id;

    // Insert vote
    $conn->query("INSERT INTO votes (voter_id, team_id) VALUES ('$voterIdInserted', '$teamId')");

    // Update team vote count
    $conn->query("UPDATE teams SET votes = votes + 1 WHERE id = '$teamId'");
}

function handleProblemVote($conn) {
    $voterName = $conn->real_escape_string($_POST['voterName']);
    $voterEmail = $conn->real_escape_string($_POST['voterEmail']);
    $voterId = $conn->real_escape_string($_POST['voterId']);
    $voterBranch = $conn->real_escape_string($_POST['voterBranch']);
    $voterYear = $conn->real_escape_string($_POST['voterYear']);
    $problemId = $conn->real_escape_string($_POST['problemId']);

    // Check if voter already voted for a problem
    $checkVoter = $conn->query("SELECT id FROM voters WHERE email = '$voterEmail' OR student_id = '$voterId'");
    if ($checkVoter->num_rows > 0) {
        $voter = $checkVoter->fetch_assoc();
        $voterId = $voter['id'];
        
        // Check if already voted for a problem
        $checkProblemVote = $conn->query("SELECT id FROM problem_votes WHERE voter_id = '$voterId'");
        if ($checkProblemVote->num_rows > 0) {
            echo "<script>alert('You have already voted for a problem statement! Each student can only vote once.');</script>";
            return;
        }
    } else {
        // Insert voter
        $conn->query("INSERT INTO voters (name, email, student_id, branch, year) VALUES ('$voterName', '$voterEmail', '$voterId', '$voterBranch', '$voterYear')");
        $voterId = $conn->insert_id;
    }

    // Insert problem vote
    $conn->query("INSERT INTO problem_votes (voter_id, problem_id) VALUES ('$voterId', '$problemId')");

    // Update problem vote count
    $conn->query("UPDATE problems SET votes = votes + 1 WHERE id = '$problemId'");
}

function handleAdminLogin() {
    $adminUsers = ["Shreya", "Admin", "Organizer", "Faculty", "Coordinator"];
    $adminName = $_POST['adminName'];
    
    if (in_array($adminName, $adminUsers)) {
        $_SESSION['admin_authenticated'] = true;
        $_SESSION['admin_name'] = $adminName;
    } else {
        echo "<script>alert('Access Denied! You are not authorized to view results.');</script>";
    }
}

function handleAdminLogout() {
    unset($_SESSION['admin_authenticated']);
    unset($_SESSION['admin_name']);
}

function getVoterDetails($conn) {
    $teamId = $conn->real_escape_string($_POST['teamId']);
    
    $result = $conn->query("
        SELECT v.name, v.branch, v.year, v.student_id 
        FROM votes vt 
        JOIN voters v ON vt.voter_id = v.id 
        WHERE vt.team_id = '$teamId'
        ORDER BY v.name
    ");
    
    $voters = [];
    while ($row = $result->fetch_assoc()) {
        $voters[] = $row;
    }
    
    header('Content-Type: application/json');
    echo json_encode($voters);
    exit;
}

// Get teams data
$teams = array();
$result = $conn->query("SELECT * FROM teams ORDER BY tier, id");
while ($row = $result->fetch_assoc()) {
    $teams[$row['tier']][] = $row;
}

// Get problems data
$problems = array();
$result = $conn->query("SELECT * FROM problems ORDER BY tier, id");
while ($row = $result->fetch_assoc()) {
    $problems[$row['tier']][] = $row;
}

// Get results for admin
$results = array();
$problemResults = array();
if (isset($_SESSION['admin_authenticated']) && $_SESSION['admin_authenticated']) {
    // Team results
    $result = $conn->query("
        SELECT t.*, COUNT(vt.id) as total_votes
        FROM teams t
        LEFT JOIN votes vt ON t.id = vt.team_id
        GROUP BY t.id
        ORDER BY t.tier, t.votes DESC
    ");
    
    while ($row = $result->fetch_assoc()) {
        $results[] = $row;
    }
    
    // Problem results
    $result = $conn->query("
        SELECT p.*, COUNT(pv.id) as total_votes
        FROM problems p
        LEFT JOIN problem_votes pv ON p.id = pv.problem_id
        GROUP BY p.id
        ORDER BY p.tier, p.votes DESC
    ");
    
    while ($row = $result->fetch_assoc()) {
        $problemResults[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hackcelerate 2025 - Voting System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #6a11cb;
            --secondary: #2575fc;
            --accent: #ff2a6d;
            --accent2: #00ff9d;
            --dark: #0c0e27;
            --darker: #070817;
            --light: #ffffff;
            --neon-glow: 0 0 10px currentColor, 0 0 20px currentColor, 0 0 40px currentColor;
            --card-bg: rgba(16, 18, 47, 0.7);
            --card-border: rgba(106, 17, 203, 0.5);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, var(--darker), var(--dark));
            color: var(--light);
            min-height: 100vh;
            padding: 15px;
            overflow-x: hidden;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
        }
        
        header {
            text-align: center;
            padding: 25px 15px;
            margin-bottom: 25px;
            position: relative;
            overflow: hidden;
            border-radius: 15px;
            background: linear-gradient(135deg, rgba(106, 17, 203, 0.2), rgba(37, 117, 252, 0.2));
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
            border: 1px solid var(--card-border);
        }
        
        header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="%236a11cb" fill-opacity="0.1" d="M0,96L48,112C96,128,192,160,288,186.7C384,213,480,235,576,213.3C672,192,768,128,864,128C960,128,1056,192,1152,197.3C1248,203,1344,149,1392,122.7L1440,96L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path></svg>');
            background-size: cover;
            background-position: center;
        }
        
        h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
            background: linear-gradient(to right, var(--accent2), var(--secondary));
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            text-shadow: 0 0 15px rgba(0, 255, 157, 0.5);
            position: relative;
        }
        
        .subtitle {
            font-size: 1.1rem;
            opacity: 0.9;
            position: relative;
            margin-bottom: 15px;
        }
        
        .event-date {
            font-size: 1rem;
            color: var(--accent2);
            text-shadow: 0 0 10px rgba(0, 255, 157, 0.5);
            position: relative;
        }
        
        .voting-options {
            display: flex;
            gap: 15px;
            margin-bottom: 25px;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .voting-option-btn {
            padding: 12px 25px;
            background: rgba(106, 17, 203, 0.3);
            border: 1px solid var(--card-border);
            border-radius: 50px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 600;
            font-size: 1rem;
            color: white;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .voting-option-btn:hover {
            background: rgba(106, 17, 203, 0.5);
            transform: translateY(-3px);
        }
        
        .voting-option-btn.active {
            background: var(--primary);
            color: white;
            box-shadow: 0 0 15px rgba(106, 17, 203, 0.7);
        }
        
        .main-content {
            display: grid;
            grid-template-columns: 1fr;
            gap: 25px;
            margin-bottom: 25px;
        }
        
        @media (min-width: 768px) {
            h1 {
                font-size: 3.5rem;
            }
            
            .subtitle {
                font-size: 1.3rem;
            }
            
            .event-date {
                font-size: 1.2rem;
            }
            
            .main-content {
                grid-template-columns: 1fr 1fr;
                gap: 30px;
            }
        }
        
        .teams-section, .problems-section {
            background: var(--card-bg);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
            border: 1px solid var(--card-border);
        }
        
        .voting-section {
            background: var(--card-bg);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
            border: 1px solid var(--card-border);
            display: none;
        }
        
        .section-title {
            font-size: 1.5rem;
            margin-bottom: 20px;
            color: var(--accent2);
            text-shadow: 0 0 10px rgba(0, 255, 157, 0.5);
            border-bottom: 2px solid var(--accent2);
            padding-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .section-title i {
            color: var(--accent2);
        }
        
        .tier-tabs {
            display: flex;
            gap: 8px;
            margin-bottom: 20px;
            flex-wrap: wrap;
            justify-content: center;
        }
        
        .tier-tab {
            padding: 10px 20px;
            background: rgba(106, 17, 203, 0.3);
            border: 1px solid var(--card-border);
            border-radius: 50px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 600;
            font-size: 0.9rem;
        }
        
        .tier-tab:hover {
            background: rgba(106, 17, 203, 0.5);
            transform: translateY(-3px);
        }
        
        .tier-tab.active {
            background: var(--primary);
            color: white;
            box-shadow: 0 0 15px rgba(106, 17, 203, 0.7);
        }
        
        .tier-content {
            display: none;
        }
        
        .tier-content.active {
            display: block;
            animation: fadeIn 0.5s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .teams-grid, .problems-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 15px;
            max-height: 500px;
            overflow-y: auto;
            padding-right: 8px;
        }
        
        @media (min-width: 480px) {
            .teams-grid, .problems-grid {
                grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            }
        }
        
        .teams-grid::-webkit-scrollbar, .problems-grid::-webkit-scrollbar {
            width: 6px;
        }
        
        .teams-grid::-webkit-scrollbar-track, .problems-grid::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 8px;
        }
        
        .teams-grid::-webkit-scrollbar-thumb, .problems-grid::-webkit-scrollbar-thumb {
            background: var(--primary);
            border-radius: 8px;
        }
        
        .team-card, .problem-card {
            background: rgba(30, 33, 70, 0.7);
            border-radius: 12px;
            padding: 15px;
            transition: all 0.3s ease;
            cursor: pointer;
            border: 1px solid transparent;
            position: relative;
            overflow: hidden;
        }
        
        .team-card::before, .problem-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(to right, var(--primary), var(--secondary));
        }
        
        .team-card:hover, .problem-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
            border-color: var(--accent2);
        }
        
        .team-card.selected, .problem-card.selected {
            border-color: var(--accent2);
            box-shadow: 0 0 15px rgba(0, 255, 157, 0.3);
        }
        
        .team-id, .problem-id {
            font-size: 1.1rem;
            font-weight: bold;
            color: var(--accent2);
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        .team-name, .problem-name {
            font-size: 1.2rem;
            margin-bottom: 8px;
            color: var(--light);
        }
        
        .team-problem, .problem-description {
            font-size: 0.85rem;
            opacity: 0.8;
            margin-bottom: 12px;
            line-height: 1.4;
            background: rgba(0, 0, 0, 0.2);
            padding: 6px;
            border-radius: 5px;
        }
        
        .team-votes, .problem-votes {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            margin-top: 12px;
        }
        
        .vote-btn {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 50px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 0.9rem;
        }
        
        .vote-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 0 12px rgba(106, 17, 203, 0.7);
        }
        
        .voting-form {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        
        label {
            font-weight: 500;
            color: var(--accent2);
            font-size: 0.95rem;
        }
        
        input, select {
            padding: 12px;
            border-radius: 8px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            background: rgba(30, 33, 70, 0.7);
            color: white;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }
        
        input:focus, select:focus {
            outline: none;
            border-color: var(--accent2);
            box-shadow: 0 0 8px rgba(0, 255, 157, 0.3);
        }
        
        .submit-btn {
            background: linear-gradient(135deg, var(--accent2), var(--secondary));
            color: var(--dark);
            border: none;
            padding: 15px;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .submit-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0, 255, 157, 0.4);
        }
        
        .results-section {
            background: var(--card-bg);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
            border: 1px solid var(--card-border);
            margin-top: 25px;
        }
        
        .results-header {
            display: flex;
            flex-direction: column;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        @media (min-width: 768px) {
            .results-header {
                flex-direction: row;
                justify-content: space-between;
                align-items: center;
            }
        }
        
        .admin-login {
            display: flex;
            flex-direction: column;
            gap: 10px;
            background: rgba(30, 33, 70, 0.7);
            padding: 15px;
            border-radius: 12px;
        }
        
        @media (min-width: 480px) {
            .admin-login {
                flex-direction: row;
                align-items: center;
            }
        }
        
        .admin-input {
            padding: 10px 12px;
            border-radius: 8px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            background: rgba(16, 18, 47, 0.7);
            color: white;
            flex: 1;
        }
        
        .admin-btn {
            background: linear-gradient(135deg, var(--accent2), var(--secondary));
            color: var(--dark);
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            white-space: nowrap;
        }
        
        .admin-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 0 12px rgba(0, 255, 157, 0.5);
        }
        
        .logout-btn {
            background: linear-gradient(135deg, var(--accent), var(--primary));
        }
        
        .results-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 15px;
        }
        
        @media (min-width: 480px) {
            .results-grid {
                grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            }
        }
        
        .result-card {
            background: rgba(30, 33, 70, 0.7);
            border-radius: 12px;
            padding: 15px;
            display: flex;
            flex-direction: column;
            gap: 10px;
            border-left: 4px solid var(--accent2);
            transition: all 0.3s ease;
        }
        
        @media (min-width: 768px) {
            .result-card {
                flex-direction: row;
                justify-content: space-between;
                align-items: center;
            }
        }
        
        .result-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }
        
        .result-team {
            font-size: 1rem;
            font-weight: bold;
            flex: 1;
        }
        
        .result-votes {
            font-size: 1.3rem;
            color: var(--accent2);
            font-weight: bold;
        }
        
        .vote-confirmation {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: var(--card-bg);
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.5);
            text-align: center;
            z-index: 1000;
            display: none;
            border: 1px solid var(--accent2);
            max-width: 90%;
            width: 400px;
        }
        
        .confirmation-title {
            font-size: 1.5rem;
            margin-bottom: 12px;
            color: var(--accent2);
        }
        
        .confirmation-text {
            margin-bottom: 20px;
            line-height: 1.5;
            font-size: 0.95rem;
        }
        
        .confirmation-buttons {
            display: flex;
            gap: 12px;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .confirm-btn, .cancel-btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 0.9rem;
        }
        
        .confirm-btn {
            background: var(--accent2);
            color: var(--dark);
        }
        
        .confirm-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 0 12px rgba(0, 255, 157, 0.5);
        }
        
        .cancel-btn {
            background: rgba(255, 42, 109, 0.2);
            color: var(--light);
            border: 1px solid var(--accent);
        }
        
        .cancel-btn:hover {
            background: var(--accent);
            transform: scale(1.05);
        }
        
        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            z-index: 999;
            display: none;
        }
        
        .pulse {
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(0, 255, 157, 0.7);
            }
            70% {
                box-shadow: 0 0 0 12px rgba(0, 255, 157, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(0, 255, 157, 0);
            }
        }
        
        .neon-text {
            text-shadow: 0 0 5px currentColor, 0 0 10px currentColor, 0 0 15px currentColor;
        }
        
        .tier-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .tier-1 {
            background: rgba(0, 255, 157, 0.2);
            color: var(--accent2);
            border: 1px solid var(--accent2);
        }
        
        .tier-2 {
            background: rgba(37, 117, 252, 0.2);
            color: var(--secondary);
            border: 1px solid var(--secondary);
        }
        
        .tier-3 {
            background: rgba(255, 42, 109, 0.2);
            color: var(--accent);
            border: 1px solid var(--accent);
        }
        
        .footer {
            text-align: center;
            margin-top: 30px;
            padding: 15px;
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.85rem;
        }
        
        .admin-message {
            text-align: center;
            margin-top: 15px;
            padding: 12px;
            background: rgba(255, 42, 109, 0.2);
            border-radius: 8px;
            font-size: 0.9rem;
        }
        
        .tier-results {
            margin-bottom: 20px;
        }
        
        .tier-results-title {
            font-size: 1.3rem;
            color: var(--accent2);
            margin-bottom: 12px;
            border-bottom: 1px solid var(--accent2);
            padding-bottom: 6px;
        }
        
        .voter-details {
            margin-top: 8px;
            font-size: 0.75rem;
            color: rgba(255, 255, 255, 0.7);
        }
        
        .success-popup {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: var(--card-bg);
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5);
            text-align: center;
            z-index: 1001;
            display: none;
            border: 2px solid var(--accent2);
            max-width: 90%;
            width: 400px;
            animation: successPopup 0.5s ease;
        }
        
        @keyframes successPopup {
            0% { transform: translate(-50%, -50%) scale(0.8); opacity: 0; }
            100% { transform: translate(-50%, -50%) scale(1); opacity: 1; }
        }
        
        .success-icon {
            font-size: 3rem;
            color: var(--accent2);
            margin-bottom: 15px;
            animation: bounce 1s ease infinite;
        }
        
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
            40% { transform: translateY(-8px); }
            60% { transform: translateY(-4px); }
        }
        
        .success-title {
            font-size: 1.5rem;
            color: var(--accent2);
            margin-bottom: 12px;
        }
        
        .success-message {
            font-size: 1rem;
            margin-bottom: 20px;
            line-height: 1.5;
        }
        
        .success-btn {
            background: linear-gradient(135deg, var(--accent2), var(--secondary));
            color: var(--dark);
            border: none;
            padding: 10px 25px;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .success-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 0 15px rgba(0, 255, 157, 0.5);
        }
        
        .mobile-warning {
            display: none;
            text-align: center;
            padding: 10px;
            background: rgba(255, 42, 109, 0.2);
            border-radius: 8px;
            margin-bottom: 15px;
            font-size: 0.9rem;
        }
        
        @media (max-width: 480px) {
            .mobile-warning {
                display: block;
            }
        }

        /* Voter Details Modal */
        .voter-details-modal {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: var(--card-bg);
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.5);
            z-index: 1002;
            display: none;
            border: 1px solid var(--accent2);
            max-width: 90%;
            width: 500px;
            max-height: 80vh;
            overflow-y: auto;
        }

        .voter-details-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            border-bottom: 1px solid var(--accent2);
            padding-bottom: 10px;
        }

        .voter-details-title {
            font-size: 1.3rem;
            color: var(--accent2);
        }

        .close-details {
            background: none;
            border: none;
            color: var(--light);
            font-size: 1.5rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .close-details:hover {
            color: var(--accent);
        }

        .voter-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .voter-item {
            background: rgba(30, 33, 70, 0.7);
            padding: 12px;
            border-radius: 8px;
        }

        .voter-name {
            font-weight: bold;
            margin-bottom: 4px;
        }

        .voter-meta {
            font-size: 0.8rem;
            color: rgba(255, 255, 255, 0.7);
        }

        .view-voters-btn {
            background: rgba(37, 117, 252, 0.2);
            color: var(--secondary);
            border: 1px solid var(--secondary);
            padding: 6px 12px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.8rem;
            transition: all 0.3s ease;
            margin-top: 8px;
        }

        .view-voters-btn:hover {
            background: var(--secondary);
            color: white;
        }

        .no-voters {
            text-align: center;
            padding: 20px;
            color: rgba(255, 255, 255, 0.7);
            font-style: italic;
        }
        
        .problem-vote-btn {
            background: linear-gradient(135deg, var(--accent), var(--primary));
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 50px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 0.9rem;
        }
        
        .problem-vote-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 0 12px rgba(255, 42, 109, 0.7);
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1 class="neon-text">HACKCELERATE 2025</h1>
            <p class="subtitle">Vote for Your Favorite Hackathon Teams & Problem Statements</p>
            <p class="event-date">Friday, 21st  November 2025</p>
        </header>
        
        <div class="voting-options">
            <button class="voting-option-btn active" id="teamVotingBtn">
                <i class="fas fa-users"></i> Vote for Teams
            </button>
            <button class="voting-option-btn" id="problemVotingBtn">
                <i class="fas fa-lightbulb"></i> Vote for Problem Statements
            </button>
        </div>
        
        <div class="main-content">
            <section class="teams-section" id="teamsSection">
                <h2 class="section-title"><i class="fas fa-users"></i> Participating Teams</h2>
                
                <div class="tier-tabs">
                    <div class="tier-tab active" data-tier="tier1">Tier 1</div>
                    <div class="tier-tab" data-tier="tier2">Tier 2</div>
                    <div class="tier-tab" data-tier="tier3">Tier 3</div>
                </div>
                
                <div class="tier-content active" id="tier1">
                    <div class="teams-grid" id="tier1Teams">
                        <?php
                        if (isset($teams['tier1'])) {
                            foreach ($teams['tier1'] as $team) {
                                echo '
                                <div class="team-card" data-team-id="' . $team['id'] . '">
                                    <div class="tier-badge tier-1">Tier 1</div>
                                    <div class="team-id">' . $team['id'] . '</div>
                                    <div class="team-name">' . $team['name'] . '</div>
                                    <div class="team-problem">' . $team['problem'] . '</div>
                                    <div class="team-votes">
                                        <button class="vote-btn" data-team-id="' . $team['id'] . '" data-tier="tier1">
                                            <i class="fas fa-vote-yea"></i> Vote for this Team
                                        </button>
                                    </div>
                                </div>';
                            }
                        }
                        ?>
                    </div>
                </div>
                
                <div class="tier-content" id="tier2">
                    <div class="teams-grid" id="tier2Teams">
                        <?php
                        if (isset($teams['tier2'])) {
                            foreach ($teams['tier2'] as $team) {
                                echo '
                                <div class="team-card" data-team-id="' . $team['id'] . '">
                                    <div class="tier-badge tier-2">Tier 2</div>
                                    <div class="team-id">' . $team['id'] . '</div>
                                    <div class="team-name">' . $team['name'] . '</div>
                                    <div class="team-problem">' . $team['problem'] . '</div>
                                    <div class="team-votes">
                                        <button class="vote-btn" data-team-id="' . $team['id'] . '" data-tier="tier2">
                                            <i class="fas fa-vote-yea"></i> Vote for this Team
                                        </button>
                                    </div>
                                </div>';
                            }
                        }
                        ?>
                    </div>
                </div>
                
                <div class="tier-content" id="tier3">
                    <div class="teams-grid" id="tier3Teams">
                        <?php
                        if (isset($teams['tier3'])) {
                            foreach ($teams['tier3'] as $team) {
                                echo '
                                <div class="team-card" data-team-id="' . $team['id'] . '">
                                    <div class="tier-badge tier-3">Tier 3</div>
                                    <div class="team-id">' . $team['id'] . '</div>
                                    <div class="team-name">' . $team['name'] . '</div>
                                    <div class="team-problem">' . $team['problem'] . '</div>
                                    <div class="team-votes">
                                        <button class="vote-btn" data-team-id="' . $team['id'] . '" data-tier="tier3">
                                            <i class="fas fa-vote-yea"></i> Vote for this Team
                                        </button>
                                    </div>
                                </div>';
                            }
                        }
                        ?>
                    </div>
                </div>
            </section>
            
            <section class="problems-section" id="problemsSection" style="display: none;">
                <h2 class="section-title"><i class="fas fa-lightbulb"></i> Problem Statements</h2>
                
                <div class="tier-tabs">
                    <div class="tier-tab active" data-tier="tier1">Tier 1</div>
                    <div class="tier-tab" data-tier="tier2">Tier 2</div>
                    <div class="tier-tab" data-tier="tier3">Tier 3</div>
                </div>
                
                <div class="tier-content active" id="problemTier1">
                    <div class="problems-grid" id="problemTier1Grid">
                        <?php
                        if (isset($problems['tier1'])) {
                            foreach ($problems['tier1'] as $problem) {
                                echo '
                                <div class="problem-card" data-problem-id="' . $problem['id'] . '">
                                    <div class="tier-badge tier-1">Tier 1</div>
                                    <div class="problem-id">' . $problem['id'] . '</div>
                                    <div class="problem-name">' . $problem['name'] . '</div>
                                    <div class="problem-description">' . $problem['description'] . '</div>
                                    <div class="problem-votes">
                                        <button class="problem-vote-btn" data-problem-id="' . $problem['id'] . '" data-tier="tier1">
                                            <i class="fas fa-vote-yea"></i> Vote for this Problem
                                        </button>
                                    </div>
                                </div>';
                            }
                        }
                        ?>
                    </div>
                </div>
                
                <div class="tier-content" id="problemTier2">
                    <div class="problems-grid" id="problemTier2Grid">
                        <?php
                        if (isset($problems['tier2'])) {
                            foreach ($problems['tier2'] as $problem) {
                                echo '
                                <div class="problem-card" data-problem-id="' . $problem['id'] . '">
                                    <div class="tier-badge tier-2">Tier 2</div>
                                    <div class="problem-id">' . $problem['id'] . '</div>
                                    <div class="problem-name">' . $problem['name'] . '</div>
                                    <div class="problem-description">' . $problem['description'] . '</div>
                                    <div class="problem-votes">
                                        <button class="problem-vote-btn" data-problem-id="' . $problem['id'] . '" data-tier="tier2">
                                            <i class="fas fa-vote-yea"></i> Vote for this Problem
                                        </button>
                                    </div>
                                </div>';
                            }
                        }
                        ?>
                    </div>
                </div>
                
                <div class="tier-content" id="problemTier3">
                    <div class="problems-grid" id="problemTier3Grid">
                        <?php
                        if (isset($problems['tier3'])) {
                            foreach ($problems['tier3'] as $problem) {
                                echo '
                                <div class="problem-card" data-problem-id="' . $problem['id'] . '">
                                    <div class="tier-badge tier-3">Tier 3</div>
                                    <div class="problem-id">' . $problem['id'] . '</div>
                                    <div class="problem-name">' . $problem['name'] . '</div>
                                    <div class="problem-description">' . $problem['description'] . '</div>
                                    <div class="problem-votes">
                                        <button class="problem-vote-btn" data-problem-id="' . $problem['id'] . '" data-tier="tier3">
                                            <i class="fas fa-vote-yea"></i> Vote for this Problem
                                        </button>
                                    </div>
                                </div>';
                            }
                        }
                        ?>
                    </div>
                </div>
            </section>
            
            <section class="voting-section" id="votingSection">
                <h2 class="section-title"><i class="fas fa-vote-yea"></i> Cast Your Vote here !!!</h2>
                <form class="voting-form" id="votingForm" method="POST">
                    <input type="hidden" name="action" value="vote" id="voteAction">
                    <input type="hidden" name="teamId" id="teamIdInput">
                    <input type="hidden" name="problemId" id="problemIdInput">
                    
                    <div class="form-group">
                        <label for="voterName"><i class="fas fa-user"></i> Your Name</label>
                        <input type="text" id="voterName" name="voterName" placeholder="Enter your full name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="voterEmail"><i class="fas fa-envelope"></i> Your Email</label>
                        <input type="email" id="voterEmail" name="voterEmail" placeholder="Enter your college email" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="voterId"><i class="fas fa-id-card"></i> USN </label>
                        <input type="text" id="voterId" name="voterId" placeholder="Enter your USN" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="voterBranch"><i class="fas fa-graduation-cap"></i> Your Branch</label>
                        <select id="voterBranch" name="voterBranch" required>
                            <option value="">Select your branch</option>
                            <option value="AIML">AI & ML</option>
                            <option value="AIDS">AI & DS</option>
                            <option value="ECE">ECE</option>
                            <option value="ISE">ISE</option>
                            <option value="EEE">EEE</option>
                            <option value="CSE">CSE</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="voterYear"><i class="fas fa-calendar-alt"></i> Your Year</label>
                        <select id="voterYear" name="voterYear" required>
                            <option value="">Select your year</option>
                            <option value="1st Year">1st Year</option>
                            <option value="2nd Year">2nd Year</option>
                            <option value="3rd Year">3rd Year</option>
                            <option value="4th Year">4th Year</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="selectedItem"><i class="fas fa-users" id="selectedItemIcon"></i> <span id="selectedItemLabel">Selected Team</span></label>
                        <input type="text" id="selectedItemDisplay" readonly style="background: rgba(0,0,0,0.3);">
                    </div>
                    
                    <button type="submit" class="submit-btn pulse">
                        <i class="fas fa-paper-plane"></i> Submit Your Vote
                    </button>
                </form>
            </section>
        </div>
        
        <section class="results-section">
            <div class="results-header">
                <h2 class="section-title"><i class="fas fa-chart-bar"></i> Live Results</h2>
                <div class="admin-login">
                    <?php if (!isset($_SESSION['admin_authenticated']) || !$_SESSION['admin_authenticated']): ?>
                        <form method="POST" style="display: contents;">
                            <input type="hidden" name="action" value="admin_login">
                            <input type="text" id="adminName" name="adminName" class="admin-input" placeholder="Enter admin name">
                            <button type="submit" class="admin-btn" id="adminLoginBtn">
                                <i class="fas fa-lock"></i> View Results
                            </button>
                        </form>
                    <?php else: ?>
                        <form method="POST" style="display: contents;">
                            <input type="hidden" name="action" value="admin_logout">
                            <button type="submit" class="admin-btn logout-btn" id="adminLogoutBtn">
                                <i class="fas fa-lock-open"></i> Close Results
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
            
            <div id="adminMessage" class="admin-message" style="<?php echo (isset($_SESSION['admin_authenticated']) && $_SESSION['admin_authenticated']) ? 'display: none;' : ''; ?>">
                <p><i class="fas fa-lock"></i> Results are hidden from general voters. Only authenticated administrators can view live results.</p>
            </div>
            
            <div class="results-grid" id="resultsGrid" style="<?php echo (isset($_SESSION['admin_authenticated']) && $_SESSION['admin_authenticated']) ? 'display: grid;' : 'display: none;'; ?>">
                <?php
                if (isset($_SESSION['admin_authenticated']) && $_SESSION['admin_authenticated'] && !empty($results)) {
                    echo '<h3 style="grid-column: 1 / -1; color: var(--accent2); margin-bottom: 15px;">Team Voting Results</h3>';
                    $currentTier = '';
                    foreach ($results as $team) {
                        if ($team['tier'] !== $currentTier) {
                            if ($currentTier !== '') {
                                echo '</div>';
                            }
                            $currentTier = $team['tier'];
                            $tierName = ($currentTier === 'tier1') ? 'Tier 1' : (($currentTier === 'tier2') ? 'Tier 2' : 'Tier 3');
                            echo '<div class="tier-results">';
                            echo '<h3 class="tier-results-title">' . $tierName . ' Results</h3>';
                        }
                        
                        echo '
                        <div class="result-card">
                            <div class="result-team">
                                <div>' . $team['id'] . ' - ' . $team['name'] . '</div>
                                <div style="font-size: 0.8rem; opacity: 0.7;">' . $team['problem'] . '</div>
                                <button class="view-voters-btn" data-team-id="' . $team['id'] . '">
                                    <i class="fas fa-eye"></i> See Who Voted (' . $team['votes'] . ' votes)
                                </button>
                            </div>
                            <div class="result-votes">' . $team['votes'] . '</div>
                        </div>';
                    }
                    if ($currentTier !== '') {
                        echo '</div>';
                    }
                    
                    // Display problem voting results
                    if (!empty($problemResults)) {
                        echo '<h3 style="grid-column: 1 / -1; color: var(--accent2); margin-top: 30px; margin-bottom: 15px;">Problem Statement Voting Results</h3>';
                        $currentTier = '';
                        foreach ($problemResults as $problem) {
                            if ($problem['tier'] !== $currentTier) {
                                if ($currentTier !== '') {
                                    echo '</div>';
                                }
                                $currentTier = $problem['tier'];
                                $tierName = ($currentTier === 'tier1') ? 'Tier 1' : (($currentTier === 'tier2') ? 'Tier 2' : 'Tier 3');
                                echo '<div class="tier-results">';
                                echo '<h3 class="tier-results-title">' . $tierName . ' Results</h3>';
                            }
                            
                            echo '
                            <div class="result-card">
                                <div class="result-team">
                                    <div>' . $problem['id'] . ' - ' . $problem['name'] . '</div>
                                </div>
                                <div class="result-votes">' . $problem['votes'] . '</div>
                            </div>';
                        }
                        if ($currentTier !== '') {
                            echo '</div>';
                        }
                    }
                } elseif (isset($_SESSION['admin_authenticated']) && $_SESSION['admin_authenticated']) {
                    echo '<div style="text-align: center; padding: 25px; color: rgba(255,255,255,0.7);">
                            <i class="fas fa-chart-bar" style="font-size: 2.5rem; margin-bottom: 12px;"></i>
                            <p>No votes have been cast yet.</p>
                          </div>';
                }
                ?>
            </div>
        </section>
        
        <div class="footer">
            <p>Department of CSE (AI & ML) | Hackcelerate 2025 Voting System</p>
            <p> 2025 | Designed for Hackathon Voting</p>
        </div>
    </div>
    
    <div class="overlay" id="overlay"></div>
    
    <div class="vote-confirmation" id="voteConfirmation">
        <h3 class="confirmation-title"><i class="fas fa-check-circle"></i> Confirm Your Vote</h3>
        <p class="confirmation-text">You are about to vote for <span id="confirmItemName" style="color: var(--accent2); font-weight: bold;"></span>.</p>
        <p class="confirmation-text">Are you sure you want to proceed?</p>
        <div class="confirmation-buttons">
            <button class="confirm-btn" id="confirmVote">
                <i class="fas fa-check"></i> Yes, Vote Now!
            </button>
            <button class="cancel-btn" id="cancelVote">
                <i class="fas fa-times"></i> Cancel
            </button>
        </div>
    </div>
    
    <div class="success-popup" id="successPopup">
        <div class="success-icon">
            <i class="fas fa-star"></i>
        </div>
        <h3 class="success-title">Vote Submitted Successfully!</h3>
        <p class="success-message" id="successMessage">Thank you for participating in Hackcelerate 2025 voting. Your vote has been recorded successfully.</p>
        <p class="success-message" style="font-size: 0.9rem; color: var(--accent2);">Have an amazing day! 🎉</p>
        <button class="success-btn" id="successOkBtn">
            <i class="fas fa-smile"></i> Awesome!
        </button>
    </div>

    <!-- Voter Details Modal -->
    <div class="voter-details-modal" id="voterDetailsModal">
        <div class="voter-details-header">
            <h3 class="voter-details-title" id="voterDetailsTitle">Voter Details</h3>
            <button class="close-details" id="closeVoterDetails">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="voter-list" id="voterList">
            <!-- Voter details will be loaded here -->
        </div>
    </div>
    
    <script>
        let selectedTeamId = null;
        let selectedTeamTier = null;
        let selectedProblemId = null;
        let selectedProblemTier = null;
        let currentVotingType = 'team'; // 'team' or 'problem'
        
        // Initialize the page
        function init() {
            // Add event listeners
            document.querySelectorAll('.tier-tab').forEach(tab => {
                tab.addEventListener('click', switchTier);
            });
            
            document.getElementById('votingForm').addEventListener('submit', handleVoteSubmit);
            document.getElementById('confirmVote').addEventListener('click', handleVoteConfirmation);
            document.getElementById('cancelVote').addEventListener('click', closeConfirmation);
            document.getElementById('successOkBtn').addEventListener('click', closeSuccessPopup);
            document.getElementById('closeVoterDetails').addEventListener('click', closeVoterDetailsModal);
            document.getElementById('teamVotingBtn').addEventListener('click', () => switchVotingType('team'));
            document.getElementById('problemVotingBtn').addEventListener('click', () => switchVotingType('problem'));
            
            // Add click events to team vote buttons
            document.querySelectorAll('.vote-btn').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const teamId = this.getAttribute('data-team-id');
                    const tier = this.getAttribute('data-tier');
                    selectTeam(teamId, tier);
                    openVoteForm(teamId, tier, 'team');
                });
            });
            
            // Add click events to problem vote buttons
            document.querySelectorAll('.problem-vote-btn').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const problemId = this.getAttribute('data-problem-id');
                    const tier = this.getAttribute('data-tier');
                    selectProblem(problemId, tier);
                    openVoteForm(problemId, tier, 'problem');
                });
            });
            
            // Add click events to view voters buttons
            document.querySelectorAll('.view-voters-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const teamId = this.getAttribute('data-team-id');
                    showVoterDetails(teamId);
                });
            });
            
            // Check if we should show success popup (after form submission)
            <?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && ($_POST['action'] === 'vote' || $_POST['action'] === 'vote_problem')): ?>
                setTimeout(() => {
                    document.getElementById('successPopup').style.display = 'block';
                    document.getElementById('overlay').style.display = 'block';
                    
                    // Update success message with voter name
                    const voterName = "<?php echo isset($_POST['voterName']) ? $_POST['voterName'] : ''; ?>";
                    if (voterName) {
                        document.getElementById('successMessage').textContent = 
                            `Thank you ${voterName} for participating in Hackcelerate 2025 voting. Your vote has been recorded successfully.`;
                    }
                }, 100);
            <?php endif; ?>
            
            // Check if admin just logged in
            <?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'admin_login' && isset($_SESSION['admin_authenticated']) && $_SESSION['admin_authenticated']): ?>
                setTimeout(() => {
                    document.getElementById('adminMessage').style.display = 'none';
                    document.getElementById('resultsGrid').style.display = 'grid';
                    alert('Welcome <?php echo $_SESSION['admin_name']; ?>! You now have access to live results.');
                }, 100);
            <?php endif; ?>
            
            // Check if admin just logged out
            <?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'admin_logout'): ?>
                setTimeout(() => {
                    document.getElementById('adminMessage').style.display = 'block';
                    document.getElementById('resultsGrid').style.display = 'none';
                    alert('You have successfully logged out. Results are now hidden.');
                }, 100);
            <?php endif; ?>
        }
        
        // Switch between voting types (team vs problem)
        function switchVotingType(type) {
            currentVotingType = type;
            
            // Update active button
            document.getElementById('teamVotingBtn').classList.toggle('active', type === 'team');
            document.getElementById('problemVotingBtn').classList.toggle('active', type === 'problem');
            
            // Show/hide sections
            document.getElementById('teamsSection').style.display = type === 'team' ? 'block' : 'none';
            document.getElementById('problemsSection').style.display = type === 'problem' ? 'block' : 'none';
            
            // Reset voting form
            document.getElementById('votingSection').style.display = 'none';
            selectedTeamId = null;
            selectedProblemId = null;
            
            // Remove selection from cards
            document.querySelectorAll('.team-card, .problem-card').forEach(card => {
                card.classList.remove('selected');
            });
        }
        
        // Switch between tiers
        function switchTier(e) {
            const tier = e.target.getAttribute('data-tier');
            
            // Update active tab
            document.querySelectorAll('.tier-tab').forEach(tab => {
                tab.classList.remove('active');
            });
            e.target.classList.add('active');
            
            // Update active content
            if (currentVotingType === 'team') {
                document.querySelectorAll('#teamsSection .tier-content').forEach(content => {
                    content.classList.remove('active');
                });
                document.getElementById(tier).classList.add('active');
            } else {
                document.querySelectorAll('#problemsSection .tier-content').forEach(content => {
                    content.classList.remove('active');
                });
                document.getElementById('problem' + tier.charAt(0).toUpperCase() + tier.slice(1)).classList.add('active');
            }
        }
        
        // Handle team selection
        function selectTeam(teamId, tier) {
            selectedTeamId = teamId;
            selectedTeamTier = tier;
            selectedProblemId = null;
            selectedProblemTier = null;
            
            // Update team card selection
            document.querySelectorAll('.team-card').forEach(card => {
                if (card.getAttribute('data-team-id') === teamId && 
                    card.closest('.tier-content').id === tier) {
                    card.classList.add('selected');
                } else {
                    card.classList.remove('selected');
                }
            });
            
            // Remove selection from problem cards
            document.querySelectorAll('.problem-card').forEach(card => {
                card.classList.remove('selected');
            });
        }
        
        // Handle problem selection
        function selectProblem(problemId, tier) {
            selectedProblemId = problemId;
            selectedProblemTier = tier;
            selectedTeamId = null;
            selectedTeamTier = null;
            
            // Update problem card selection
            document.querySelectorAll('.problem-card').forEach(card => {
                if (card.getAttribute('data-problem-id') === problemId && 
                    card.closest('.tier-content').id === 'problem' + tier.charAt(0).toUpperCase() + tier.slice(1)) {
                    card.classList.add('selected');
                } else {
                    card.classList.remove('selected');
                }
            });
            
            // Remove selection from team cards
            document.querySelectorAll('.team-card').forEach(card => {
                card.classList.remove('selected');
            });
        }
        
        // Open vote form when vote button is clicked
        function openVoteForm(itemId, tier, type) {
            let itemName, itemDisplay;
            
            if (type === 'team') {
                // Find team name
                const teamCard = document.querySelector(`.team-card[data-team-id="${itemId}"]`);
                itemName = teamCard.querySelector('.team-name').textContent;
                itemDisplay = `${itemId} - ${itemName}`;
                
                // Update form for team voting
                document.getElementById('voteAction').value = 'vote';
                document.getElementById('teamIdInput').value = itemId;
                document.getElementById('problemIdInput').value = '';
                document.getElementById('selectedItemIcon').className = 'fas fa-users';
                document.getElementById('selectedItemLabel').textContent = 'Selected Team';
            } else {
                // Find problem name
                const problemCard = document.querySelector(`.problem-card[data-problem-id="${itemId}"]`);
                itemName = problemCard.querySelector('.problem-name').textContent;
                itemDisplay = `${itemId} - ${itemName}`;
                
                // Update form for problem voting
                document.getElementById('voteAction').value = 'vote_problem';
                document.getElementById('problemIdInput').value = itemId;
                document.getElementById('teamIdInput').value = '';
                document.getElementById('selectedItemIcon').className = 'fas fa-lightbulb';
                document.getElementById('selectedItemLabel').textContent = 'Selected Problem';
            }
            
            // Show voting section
            document.getElementById('votingSection').style.display = 'block';
            
            // Set the selected item in display
            document.getElementById('selectedItemDisplay').value = itemDisplay;
            
            // Scroll to voting section
            document.getElementById('votingSection').scrollIntoView({ behavior: 'smooth' });
        }
        
        // Handle vote form submission
        function handleVoteSubmit(e) {
            e.preventDefault();
            
            if (currentVotingType === 'team' && !selectedTeamId) {
                alert('Please select a team to vote for!');
                return;
            }
            
            if (currentVotingType === 'problem' && !selectedProblemId) {
                alert('Please select a problem to vote for!');
                return;
            }
            
            let itemName, itemDisplay;
            
            if (currentVotingType === 'team') {
                const teamCard = document.querySelector(`.team-card[data-team-id="${selectedTeamId}"]`);
                itemName = teamCard.querySelector('.team-name').textContent;
                itemDisplay = `${selectedTeamId} - ${itemName}`;
            } else {
                const problemCard = document.querySelector(`.problem-card[data-problem-id="${selectedProblemId}"]`);
                itemName = problemCard.querySelector('.problem-name').textContent;
                itemDisplay = `${selectedProblemId} - ${itemName}`;
            }
            
            document.getElementById('confirmItemName').textContent = itemDisplay;
            
            // Show confirmation dialog
            openConfirmation();
        }
        
        // Open confirmation dialog
        function openConfirmation() {
            document.getElementById('voteConfirmation').style.display = 'block';
            document.getElementById('overlay').style.display = 'block';
        }
        
        // Close confirmation dialog
        function closeConfirmation() {
            document.getElementById('voteConfirmation').style.display = 'none';
            document.getElementById('overlay').style.display = 'none';
        }
        
        // Handle vote confirmation
        function handleVoteConfirmation() {
            // Submit the form
            document.getElementById('votingForm').submit();
        }
        
        // Close success popup
        function closeSuccessPopup() {
            document.getElementById('successPopup').style.display = 'none';
            document.getElementById('overlay').style.display = 'none';
            
            // Reset form and hide voting section
            document.getElementById('votingForm').reset();
            document.getElementById('votingSection').style.display = 'none';
            selectedTeamId = null;
            selectedTeamTier = null;
            selectedProblemId = null;
            selectedProblemTier = null;
            
            // Remove selection from cards
            document.querySelectorAll('.team-card, .problem-card').forEach(card => {
                card.classList.remove('selected');
            });
            
            // Switch back to team voting view
            switchVotingType('team');
            
            // Scroll to top
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
        
        // Show voter details
        async function showVoterDetails(teamId) {
            try {
                const formData = new FormData();
                formData.append('action', 'get_voter_details');
                formData.append('teamId', teamId);
                
                const response = await fetch('', {
                    method: 'POST',
                    body: formData
                });
                
                const voters = await response.json();
                
                // Find team name
                const teamCard = document.querySelector(`.result-card .view-voters-btn[data-team-id="${teamId}"]`).closest('.result-team');
                const teamName = teamCard.querySelector('div:first-child').textContent;
                
                document.getElementById('voterDetailsTitle').textContent = `Voters for ${teamName}`;
                const voterList = document.getElementById('voterList');
                voterList.innerHTML = '';
                
                if (voters.length > 0) {
                    voters.forEach(voter => {
                        const voterItem = document.createElement('div');
                        voterItem.className = 'voter-item';
                        voterItem.innerHTML = `
                            <div class="voter-name">${voter.name}</div>
                            <div class="voter-meta">${voter.year}, ${voter.branch}</div>
                            <div class="voter-meta">Student ID: ${voter.student_id}</div>
                        `;
                        voterList.appendChild(voterItem);
                    });
                } else {
                    voterList.innerHTML = '<div class="no-voters">No voters found for this team.</div>';
                }
                
                document.getElementById('voterDetailsModal').style.display = 'block';
                document.getElementById('overlay').style.display = 'block';
            } catch (error) {
                console.error('Error fetching voter details:', error);
                alert('Error loading voter details. Please try again.');
            }
        }
        
        // Close voter details modal
        function closeVoterDetailsModal() {
            document.getElementById('voterDetailsModal').style.display = 'none';
            document.getElementById('overlay').style.display = 'none';
        }
        
        // Initialize the application
        init();
    </script>
</body>
</html>
<?php
$conn->close();
?>