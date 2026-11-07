CREATE TABLE IF NOT EXISTS prerrequisitos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    curso_id INT NOT NULL,
    requiere_codigo VARCHAR(50) NOT NULL,
    UNIQUE KEY (curso_id, requiere_codigo),
    FOREIGN KEY (curso_id) REFERENCES cursos(id) ON DELETE CASCADE
);
