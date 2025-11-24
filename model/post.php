<?php
class Post {
    private ?int $id;
    private string $author;
    private string $content;
    private string $created_at;

    public function __construct($id, $author, $content, $created_at = null) {
        $this->id = $id;
        $this->author = $author;
        $this->content = $content;
        $this->created_at = $created_at ?? date('Y-m-d H:i:s');
    }
    public function getId() { return $this->id; }
    public function getAuthor() { return $this->author; }
    public function getContent() { return $this->content; }
    public function getCreatedAt() { return $this->created_at; }
    public function setAuthor($a) { $this->author = $a; }
    public function setContent($c) { $this->content = $c; }
}
