<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Produits - EcoTrack</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #10b981;
            --primary-dark: #059669;
            --secondary-color: #3b82f6;
            --danger-color: #ef4444;
            --warning-color: #f59e0b;
            --success-color: #22c55e;
            --dark-color: #1f2937;
            --light-color: #f9fafb;
            --gray-light: #e5e7eb;
            --gray-medium: #9ca3af;
            --gray-dark: #4b5563;
            --sidebar-width: 260px;
            --border-radius: 12px;
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.12);
            --shadow-md: 0 4px 6px -1px rgba(0,0,0,0.1);
            --shadow-lg: 0 10px 15px -3px rgba(0,0,0,0.1);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f6f7f9 0%, #eef2f7 100%);
            color: var(--dark-color);
            line-height: 1.6;
            min-height: 100vh;
        }

        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: var(--sidebar-width);
            background: linear-gradient(180deg, var(--dark-color) 0%, #111827 100%);
            color: white;
            display: flex;
            flex-direction: column;
            position: fixed;
            height: 100vh;
            z-index: 1000;
            box-shadow: var(--shadow-lg);
            transition: var(--transition);
        }

        .sidebar-header {
            padding: 24px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 24px;
            font-weight: 700;
            color: white;
        }

        .logo i {
            color: var(--primary-color);
            font-size: 28px;
        }

        .sidebar-subtitle {
            font-size: 12px;
            color: var(--gray-medium);
            margin-top: 4px;
            letter-spacing: 1px;
        }

        .sidebar-nav {
            flex: 1;
            padding: 24px 0;
        }

        .sidebar-nav ul {
            list-style: none;
        }

        .nav-item {
            margin: 4px 16px;
            border-radius: var(--border-radius);
            overflow: hidden;
            transition: var(--transition);
        }

        .nav-item.active {
            background: rgba(16, 185, 129, 0.15);
            border-left: 4px solid var(--primary-color);
        }

        .nav-item:hover:not(.active) {
            background: rgba(255,255,255,0.05);
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 16px 20px;
            color: white;
            text-decoration: none;
            transition: var(--transition);
        }

        .nav-link i {
            width: 20px;
            text-align: center;
            font-size: 18px;
        }

        .nav-item.active .nav-link {
            color: var(--primary-color);
        }

        .sidebar-footer {
            padding: 24px;
            border-top: 1px solid rgba(255,255,255,0.1);
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            background: rgba(16, 185, 129, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid var(--primary-color);
        }

        .user-details {
            display: flex;
            flex-direction: column;
        }

        .user-name {
            font-weight: 600;
        }

        .user-role {
            font-size: 12px;
            color: var(--gray-medium);
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: var(--sidebar-width);
            padding: 24px;
            min-height: 100vh;
        }

        /* Header */
        .content-header {
            background: white;
            border-radius: var(--border-radius);
            padding: 24px;
            margin-bottom: 24px;
            box-shadow: var(--shadow-md);
            animation: slideDown 0.5s ease-out;
        }

        .header-left h1 {
            font-size: 28px;
            font-weight: 700;
            color: var(--dark-color);
            margin-bottom: 8px;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .header-left p {
            color: var(--gray-dark);
            font-size: 14px;
        }

        /* Tabs d'Optimisation Écologique */
        .ecoTabs {
            background: white;
            border-radius: var(--border-radius);
            margin-bottom: 24px;
            overflow: hidden;
            box-shadow: var(--shadow-md);
        }

        .tab-nav {
            display: flex;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border-bottom: 1px solid var(--gray-light);
        }

        .tab-btn {
            padding: 16px 24px;
            background: none;
            border: none;
            font-weight: 500;
            color: var(--gray-dark);
            cursor: pointer;
            transition: var(--transition);
            position: relative;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .tab-btn i {
            font-size: 14px;
        }

        .tab-btn.active {
            color: var(--primary-color);
            background: white;
            border-bottom: 3px solid var(--primary-color);
        }

        .tab-btn:hover:not(.active) {
            background: rgba(16, 185, 129, 0.05);
        }

        .tab-content {
            display: none;
            padding: 24px;
            animation: fadeIn 0.3s ease-out;
        }

        .tab-content.active {
            display: block;
        }

        /* Statistiques */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 24px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-sm);
            transition: var(--transition);
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-md);
        }

        .stat-value {
            font-size: 28px;
            font-weight: 700;
            color: var(--dark-color);
        }

        .stat-label {
            color: var(--gray-dark);
            font-size: 14px;
            margin-top: 8px;
        }

        /* Cards Styles */
        .impact-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 20px;
            margin-bottom: 16px;
            box-shadow: var(--shadow-sm);
            border-left: 4px solid var(--primary-color);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
        }

        .card-title {
            font-weight: 600;
            color: var(--dark-color);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .impact-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .high-impact {
            background: #fee2e2;
            color: #dc2626;
        }

        .medium-impact {
            background: #fef3c7;
            color: #d97706;
        }

        .low-impact {
            background: #d1fae5;
            color: #059669;
        }

        /* Graph Containers */
        .chart-container {
            background: white;
            border-radius: var(--border-radius);
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: var(--shadow-sm);
        }

        /* Table Styles */
        .modern-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 16px;
        }

        .modern-table th {
            padding: 12px;
            text-align: left;
            font-weight: 600;
            color: var(--gray-dark);
            border-bottom: 2px solid var(--gray-light);
        }

        .modern-table td {
            padding: 12px;
            border-bottom: 1px solid var(--gray-light);
        }

        .modern-table tr:hover {
            background: #f9fafb;
        }

        /* Recommendation Cards */
        .recommendation-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 16px;
            margin-top: 16px;
        }

        .recommendation-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 20px;
            box-shadow: var(--shadow-sm);
            border-left: 4px solid;
        }

        .recommendation-add {
            border-left-color: var(--success-color);
        }

        .recommendation-remove {
            border-left-color: var(--danger-color);
        }

        /* Buttons */
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: var(--border-radius);
            font-weight: 500;
            font-size: 14px;
            cursor: pointer;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            color: white;
        }

        .btn-secondary {
            background: var(--gray-light);
            color: var(--dark-color);
        }

        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .sidebar {
                width: 80px;
            }
            
            .sidebar .logo span,
            .sidebar-subtitle,
            .nav-link span,
            .user-details {
                display: none;
            }
            
            .main-content {
                margin-left: 80px;
            }
        }

        @media (max-width: 768px) {
            .tab-nav {
                flex-wrap: wrap;
            }
            
            .tab-btn {
                flex: 1;
                min-width: 120px;
                justify-content: center;
            }
            
            .recommendation-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <div class="logo">
                    <i class="fas fa-leaf"></i>
                    <span>EcoTrack</span>
                </div>
                <div class="sidebar-subtitle">Gestion Écologique</div>
            </div>
            
            <nav class="sidebar-nav">
                <ul>
                    <li class="nav-item active">
                        <a href="#dashboard-produits" class="nav-link">
                            <i class="fas fa-chart-line"></i>
                            <span>Dashboard Produits</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#produits" class="nav-link">
                            <i class="fas fa-box"></i>
                            <span>Gestion Produits</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#dechets" class="nav-link">
                            <i class="fas fa-trash"></i>
                            <span>Gestion Déchets</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#analytics" class="nav-link">
                            <i class="fas fa-chart-pie"></i>
                            <span>Analytics</span>
                        </a>
                    </li>
                </ul>
            </nav>
            
            <div class="sidebar-footer">
                <div class="user-info">
                    <div class="user-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="user-details">
                        <span class="user-name">Éco-Manager</span>
                        <span class="user-role">Responsable RSE</span>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Header -->
            <header class="content-header">
                <div class="header-left">
                    <h1><i class="fas fa-industry"></i> Dashboard Produits Polluants</h1>
                    <p>Analyse avancée de l'impact environnemental de vos produits et déchets</p>
                </div>
                <div class="header-actions">
                    <button class="btn btn-primary" id="generateReport">
                        <i class="fas fa-file-pdf"></i>
                        Générer Rapport
                    </button>
                </div>
            </header>

            <!-- Statistiques -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-value" id="totalProducts"><?= $stats['total_produits'] ?? 0 ?></div>
                    <div class="stat-label">Produits Analysés</div>
                    <div style="height: 4px; background: var(--gray-light); border-radius: 2px; margin-top: 8px;">
                        <div style="width: 85%; height: 100%; background: var(--primary-color); border-radius: 2px;"></div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-value"><?= number_format($stats['empreinte_moyenne'] ?? 0, 1) ?> <small>kgCO2</small></div>
                    <div class="stat-label">Empreinte Moyenne</div>
                    <div style="height: 4px; background: var(--gray-light); border-radius: 2px; margin-top: 8px;">
                        <div style="width: 65%; height: 100%; background: var(--warning-color); border-radius: 2px;"></div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-value"><?= $stats['dechets_produits'] ?? 0 ?> <small>kg</small></div>
                    <div class="stat-label">Déchets Générés</div>
                    <div style="height: 4px; background: var(--gray-light); border-radius: 2px; margin-top: 8px;">
                        <div style="width: 72%; height: 100%; background: var(--danger-color); border-radius: 2px;"></div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-value"><?= $stats['taux_recyclage'] ?? 0 ?>%</div>
                    <div class="stat-label">Taux de Recyclage</div>
                    <div style="height: 4px; background: var(--gray-light); border-radius: 2px; margin-top: 8px;">
                        <div style="width: <?= $stats['taux_recyclage'] ?? 0 ?>%; height: 100%; background: var(--success-color); border-radius: 2px;"></div>
                    </div>
                </div>
            </div>

            <!-- Tabs d'Optimisation Écologique -->
            <div class="ecoTabs">
                <div class="tab-nav">
                    <button class="tab-btn active" data-tab="impact">
                        <i class="fas fa-chart-bar"></i>
                        Analyse d'Impact
                    </button>
                    <button class="tab-btn" data-tab="substitution">
                        <i class="fas fa-exchange-alt"></i>
                        Substitution Écologique
                    </button>
                    <button class="tab-btn" data-tab="recyclage">
                        <i class="fas fa-recycle"></i>
                        Optimisation Recyclage
                    </button>
                    <button class="tab-btn" data-tab="reduction">
                        <i class="fas fa-arrow-down"></i>
                        Stratégies Réduction
                    </button>
                </div>

                <!-- Onglet 1: Analyse d'Impact -->
                <div class="tab-content active" id="impact-tab">
                    <div class="chart-container">
                        <h3 class="card-title">
                            <i class="fas fa-fire" style="color: var(--danger-color);"></i>
                            Empreinte Carbone par Catégorie
                        </h3>
                        <canvas id="categoryImpactChart" height="200"></canvas>
                    </div>

                    <div class="impact-card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-exclamation-triangle" style="color: var(--warning-color);"></i>
                                Produits à Haute Empreinte
                            </h3>
                            <span class="impact-badge high-impact">5 Produits</span>
                        </div>
                        <div style="overflow-x: auto;">
                            <table class="modern-table">
                                <thead>
                                    <tr>
                                        <th>Produit</th>
                                        <th>Catégorie</th>
                                        <th>Empreinte (kgCO2)</th>
                                        <th>Impact</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Smartphone Pro Max</td>
                                        <td>Électronique</td>
                                        <td>85.2</td>
                                        <td><span class="impact-badge high-impact">Très Élevé</span></td>
                                    </tr>
                                    <tr>
                                        <td>Ordinateur Portable Gaming</td>
                                        <td>Électronique</td>
                                        <td>72.4</td>
                                        <td><span class="impact-badge high-impact">Très Élevé</span></td>
                                    </tr>
                                    <tr>
                                        <td>Canapé Cuir</td>
                                        <td>Mobilier</td>
                                        <td>45.8</td>
                                        <td><span class="impact-badge medium-impact">Élevé</span></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Onglet 2: Substitution Écologique -->
                <div class="tab-content" id="substitution-tab">
                    <div class="impact-card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-lightbulb" style="color: var(--primary-color);"></i>
                                Recommandations de Substitution
                            </h3>
                            <span class="impact-badge low-impact">8 Alternatives</span>
                        </div>
                        
                        <div class="recommendation-grid">
                            <div class="recommendation-card recommendation-add">
                                <div class="card-header">
                                    <h4 style="font-weight: 600; color: var(--dark-color);">
                                        <i class="fas fa-check-circle" style="color: var(--success-color);"></i>
                                        Alternative Recommandée
                                    </h4>
                                    <span class="impact-badge low-impact">-80% CO2</span>
                                </div>
                                <div style="margin: 12px 0;">
                                    <div style="font-weight: 600; color: var(--dark-color); margin-bottom: 4px;">
                                        Produit actuel : Bouteille plastique
                                    </div>
                                    <div style="color: var(--gray-dark); font-size: 14px;">
                                        Empreinte : 0.5 kgCO2 • Usage unique
                                    </div>
                                </div>
                                <div style="margin: 12px 0;">
                                    <div style="font-weight: 600; color: var(--dark-color); margin-bottom: 4px;">
                                        Alternative : Gourde inox
                                    </div>
                                    <div style="color: var(--gray-dark); font-size: 14px;">
                                        Empreinte : 0.1 kgCO2 • Réutilisable
                                    </div>
                                </div>
                                <button class="btn btn-primary" style="width: 100%; margin-top: 12px;">
                                    <i class="fas fa-check"></i>
                                    Appliquer cette substitution
                                </button>
                            </div>

                            <div class="recommendation-card recommendation-add">
                                <div class="card-header">
                                    <h4 style="font-weight: 600; color: var(--dark-color);">
                                        <i class="fas fa-check-circle" style="color: var(--success-color);"></i>
                                        Alternative Recommandée
                                    </h4>
                                    <span class="impact-badge low-impact">-70% CO2</span>
                                </div>
                                <div style="margin: 12px 0;">
                                    <div style="font-weight: 600; color: var(--dark-color); margin-bottom: 4px;">
                                        Produit actuel : Sacs plastique
                                    </div>
                                    <div style="color: var(--gray-dark); font-size: 14px;">
                                        Empreinte : 0.2 kgCO2 • Jetable
                                    </div>
                                </div>
                                <div style="margin: 12px 0;">
                                    <div style="font-weight: 600; color: var(--dark-color); margin-bottom: 4px;">
                                        Alternative : Tote bag coton
                                    </div>
                                    <div style="color: var(--gray-dark); font-size: 14px;">
                                        Empreinte : 0.06 kgCO2 • Réutilisable
                                    </div>
                                </div>
                                <button class="btn btn-primary" style="width: 100%; margin-top: 12px;">
                                    <i class="fas fa-check"></i>
                                    Appliquer cette substitution
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Onglet 3: Optimisation Recyclage -->
                <div class="tab-content" id="recyclage-tab">
                    <div class="chart-container">
                        <h3 class="card-title">
                            <i class="fas fa-chart-pie" style="color: var(--secondary-color);"></i>
                            Taux de Recyclage par Type de Déchet
                        </h3>
                        <canvas id="recyclingChart" height="200"></canvas>
                    </div>

                    <div class="impact-card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-cogs" style="color: var(--warning-color);"></i>
                                Points d'Amélioration du Recyclage
                            </h3>
                            <span class="impact-badge medium-impact">3 Actions Prioritaires</span>
                        </div>
                        
                        <div style="margin-top: 16px;">
                            <div style="padding: 12px; background: #fef3c7; border-radius: 8px; margin-bottom: 8px;">
                                <div style="font-weight: 600; color: #92400e; margin-bottom: 4px;">
                                    <i class="fas fa-exclamation-triangle"></i> Plastique mal trié
                                </div>
                                <div style="color: #92400e; font-size: 14px;">
                                    3.2kg de plastique recyclable jeté avec les ordures ménagères
                                </div>
                            </div>
                            
                            <div style="padding: 12px; background: #fee2e2; border-radius: 8px; margin-bottom: 8px;">
                                <div style="font-weight: 600; color: #991b1b; margin-bottom: 4px;">
                                    <i class="fas fa-fire"></i> Électronique non recyclé
                                </div>
                                <div style="color: #991b1b; font-size: 14px;">
                                    2 appareils électroniques jetés au lieu d'être recyclés
                                </div>
                            </div>
                            
                            <div style="padding: 12px; background: #d1fae5; border-radius: 8px;">
                                <div style="font-weight: 600; color: #065f46; margin-bottom: 4px;">
                                    <i class="fas fa-check-circle"></i> Bonne pratique : Verre
                                </div>
                                <div style="color: #065f46; font-size: 14px;">
                                    100% du verre correctement recyclé ce mois-ci
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Onglet 4: Stratégies Réduction -->
                <div class="tab-content" id="reduction-tab">
                    <div class="impact-card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-bullseye" style="color: var(--danger-color);"></i>
                                Objectifs de Réduction d'Impact
                            </h3>
                            <span class="impact-badge high-impact">-20% Objectif</span>
                        </div>
                        
                        <div style="margin-top: 16px;">
                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px;">
                                <div style="text-align: center;">
                                    <div style="font-size: 24px; font-weight: 700; color: var(--danger-color);">-15%</div>
                                    <div style="font-size: 12px; color: var(--gray-medium);">Empreinte Carbone</div>
                                </div>
                                <div style="text-align: center;">
                                    <div style="font-size: 24px; font-weight: 700; color: var(--success-color);">+25%</div>
                                    <div style="font-size: 12px; color: var(--gray-medium);">Produits Éco-responsables</div>
                                </div>
                                <div style="text-align: center;">
                                    <div style="font-size: 24px; font-weight: 700; color: var(--primary-color);">-30%</div>
                                    <div style="font-size: 12px; color: var(--gray-medium);">Déchets Non Recyclables</div>
                                </div>
                            </div>
                            
                            <div style="background: #f8fafc; padding: 16px; border-radius: 8px;">
                                <div style="font-weight: 600; color: var(--dark-color); margin-bottom: 12px;">
                                    <i class="fas fa-calendar-check" style="color: var(--primary-color);"></i>
                                    Plan d'Action Mensuel
                                </div>
                                <div style="display: flex; flex-direction: column; gap: 8px;">
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <i class="fas fa-check-circle" style="color: var(--success-color);"></i>
                                        <span style="color: var(--gray-dark);">Semaine 1 : Audit des produits à haute empreinte</span>
                                    </div>
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <i class="fas fa-spinner" style="color: var(--warning-color);"></i>
                                        <span style="color: var(--gray-dark);">Semaine 2 : Mise en place des substitutions</span>
                                    </div>
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <i class="far fa-circle" style="color: var(--gray-medium);"></i>
                                        <span style="color: var(--gray-dark);">Semaine 3 : Formation au recyclage</span>
                                    </div>
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <i class="far fa-circle" style="color: var(--gray-medium);"></i>
                                        <span style="color: var(--gray-dark);">Semaine 4 : Évaluation des résultats</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="recommendation-grid">
                        <div class="recommendation-card recommendation-remove">
                            <div class="card-header">
                                <h4 style="font-weight: 600; color: var(--dark-color);">
                                    <i class="fas fa-minus-circle" style="color: var(--danger-color);"></i>
                                    Produits à Éliminer
                                </h4>
                                <span class="impact-badge high-impact">Priorité 1</span>
                            </div>
                            <div style="margin: 12px 0;">
                                <div style="font-weight: 600; color: var(--dark-color); margin-bottom: 4px;">
                                    Couverts plastique "biodégradables"
                                </div>
                                <div style="color: var(--gray-dark); font-size: 14px;">
                                    Empreinte : 0.3 kgCO2 • Rotation : 2.1 • Alternative : couverts réutilisables
                                </div>
                            </div>
                        </div>

                        <div class="recommendation-card recommendation-add">
                            <div class="card-header">
                                <h4 style="font-weight: 600; color: var(--dark-color);">
                                    <i class="fas fa-plus-circle" style="color: var(--success-color);"></i>
                                    Produits à Promouvoir
                                </h4>
                                <span class="impact-badge low-impact">Impact faible</span>
                            </div>
                            <div style="margin: 12px 0;">
                                <div style="font-weight: 600; color: var(--dark-color); margin-bottom: 4px;">
                                    Tasse café réutilisable
                                </div>
                                <div style="color: var(--gray-dark); font-size: 14px;">
                                    Empreinte : 0.05 kgCO2 • Rotation : 8.5 • Réduction : -95% vs gobelets jetables
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Alertes et Notifications -->
            <div class="impact-card" style="margin-top: 24px;">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-bell" style="color: var(--warning-color);"></i>
                        Alertes Écologiques en Temps Réel
                    </h3>
                    <span class="impact-badge high-impact">3 Alertes Actives</span>
                </div>
                
                <div style="margin-top: 16px;">
                    <div style="display: flex; flex-direction: column; gap: 12px;">
                        <div style="padding: 12px; background: #fee2e2; border-radius: 8px; border-left: 4px solid var(--danger-color);">
                            <div style="font-weight: 600; color: #991b1b; margin-bottom: 4px;">
                                <i class="fas fa-fire"></i> Alerte : Nouveau produit très polluant détecté
                            </div>
                            <div style="color: #991b1b; font-size: 14px;">
                                "Batterie externe 50000mAh" - Empreinte : 120 kgCO2 (2x la moyenne)
                            </div>
                        </div>
                        
                        <div style="padding: 12px; background: #fef3c7; border-radius: 8px; border-left: 4px solid var(--warning-color);">
                            <div style="font-weight: 600; color: #92400e; margin-bottom: 4px;">
                                <i class="fas fa-exclamation-triangle"></i> Attention : Augmentation des déchets plastique
                            </div>
                            <div style="color: #92400e; font-size: 14px;">
                                +25% de déchets plastique ce mois-ci vs mois précédent
                            </div>
                        </div>
                        
                        <div style="padding: 12px; background: #d1fae5; border-radius: 8px; border-left: 4px solid var(--success-color);">
                            <div style="font-weight: 600; color: #065f46; margin-bottom: 4px;">
                                <i class="fas fa-trophy"></i> Succès : Objectif de recyclage atteint
                            </div>
                            <div style="color: #065f46; font-size: 14px;">
                                Taux de recyclage du papier : 95% (objectif : 90%)
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Initialisation des graphiques
        function initCharts() {
            // Graphique Empreinte par Catégorie
            const categoryCtx = document.getElementById('categoryImpactChart').getContext('2d');
            new Chart(categoryCtx, {
                type: 'bar',
                data: {
                    labels: ['Électronique', 'Textile', 'Alimentaire', 'Cosmétique', 'Mobilier', 'Emballage'],
                    datasets: [{
                        label: 'Empreinte Carbone (kgCO2)',
                        data: [85, 29, 15, 25, 42, 18],
                        backgroundColor: [
                            '#ef4444',
                            '#f59e0b',
                            '#10b981',
                            '#8b5cf6',
                            '#3b82f6',
                            '#6b7280'
                        ],
                        borderRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top',
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'kgCO2'
                            }
                        }
                    }
                }
            });

            // Graphique Recyclage
            const recyclingCtx = document.getElementById('recyclingChart').getContext('2d');
            new Chart(recyclingCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Plastique', 'Papier', 'Verre', 'Métal', 'Électronique'],
                    datasets: [{
                        data: [45, 85, 100, 92, 38],
                        backgroundColor: [
                            '#3b82f6',
                            '#f59e0b',
                            '#10b981',
                            '#6b7280',
                            '#ef4444'
                        ],
                        borderWidth: 2,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom',
                        }
                    }
                }
            });
        }

        // Gestion des onglets
        document.addEventListener('DOMContentLoaded', function() {
            initCharts();
            
            // Gestionnaire d'onglets
            const tabBtns = document.querySelectorAll('.tab-btn');
            const tabContents = document.querySelectorAll('.tab-content');
            
            tabBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const tabId = this.dataset.tab;
                    
                    // Retirer la classe active de tous les boutons et contenus
                    tabBtns.forEach(b => b.classList.remove('active'));
                    tabContents.forEach(c => c.classList.remove('active'));
                    
                    // Activer l'onglet cliqué
                    this.classList.add('active');
                    document.getElementById(`${tabId}-tab`).classList.add('active');
                });
            });
            
            // Bouton de génération de rapport
            document.getElementById('generateReport').addEventListener('click', function() {
                const icon = this.querySelector('i');
                icon.classList.remove('fa-file-pdf');
                icon.classList.add('fa-spinner', 'fa-spin');
                
                setTimeout(() => {
                    icon.classList.remove('fa-spinner', 'fa-spin');
                    icon.classList.add('fa-file-pdf');
                    alert('Rapport écologique généré avec succès !');
                }, 2000);
            });
            
            // Boutons d'application des substitutions
            document.querySelectorAll('.btn-primary').forEach(btn => {
                if (btn.textContent.includes('Appliquer')) {
                    btn.addEventListener('click', function() {
                        const productName = this.closest('.recommendation-card').querySelector('div[style*="font-weight: 600"]').textContent;
                        alert(`Substitution appliquée pour : ${productName}`);
                    });
                }
            });
            
            // Simulation de données en temps réel
            function simulateRealTimeData() {
                // Mettre à jour les compteurs périodiquement
                setTimeout(simulateRealTimeData, 30000);
            }
            
            simulateRealTimeData();
        });
    </script>
</body>
</html>