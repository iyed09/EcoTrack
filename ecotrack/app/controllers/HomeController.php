<?php
class HomeController extends Controller {
    public function index() {
        $data = [
            'pageTitle' => 'Home',
            'isLoggedIn' => $this->isLoggedIn()
        ];
        $this->render('home/index', $data);
    }
}
