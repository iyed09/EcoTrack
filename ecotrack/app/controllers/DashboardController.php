<?php
class DashboardController extends Controller {
    public function index() {
        $this->requireLogin();

        $userId = $_SESSION['user_id'];

        $energyModel = new EnergyConsumption();
        $transportModel = new TransportEntry();
        $wasteModel = new WasteEntry();
        $reportModel = new TrashReport();

        $energyEmissions = $energyModel->getTotalEmissions($userId);
        $transportEmissions = $transportModel->getTotalEmissions($userId);
        $wasteImpact = $wasteModel->getTotalImpact($userId);
        $totalReports = $reportModel->countByUser($userId);

        $recentEnergyData = $energyModel->getRecentByUser($userId, 5);
        $recentTransportData = $transportModel->getRecentByUser($userId, 5);

        $data = [
            'pageTitle' => 'Dashboard',
            'userName' => $_SESSION['user_name'],
            'energyEmissions' => $energyEmissions,
            'transportEmissions' => $transportEmissions,
            'wasteImpact' => $wasteImpact,
            'totalReports' => $totalReports,
            'recentEnergyData' => $recentEnergyData,
            'recentTransportData' => $recentTransportData,
            'isLoggedIn' => true,
            'isAdmin' => $this->isAdmin()
        ];

        $this->render('dashboard/index', $data);
    }
}
