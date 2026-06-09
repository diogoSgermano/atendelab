<?php
class PessoasController
{
    private PDO $pdo;

    public function __construct()
    {
        require __DIR__ . '/../../config/database.php';
        $this->pdo = $pdo;
    }

    public function listar(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $sql = 'SELECT id_pessoa, nome, cpf, data_nascimento, altura, peso, sexo, status, criado_em
                FROM pessoas
                ORDER BY id_pessoa DESC';

        $stmt = $this->pdo->query($sql);
        $pessoas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($pessoas, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    public function buscarPorId(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

        if (!$id) {
            http_response_code(400);
            echo json_encode(['erro' => 'ID inválido.']);
            return;
        }

        $sql = 'SELECT id_pessoa, nome, cpf, data_nascimento, altura, peso, sexo, status, criado_em
                FROM pessoas
                WHERE id_pessoa = :id';

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $pessoa = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$pessoa) {
            http_response_code(404);
            echo json_encode(['erro' => 'Pessoa não encontrada.']);
            return;
        }

        echo json_encode($pessoa, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    public function criar(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $nome = trim($_POST['nome'] ?? '');
        $cpf = trim($_POST['cpf'] ?? '');
        $data_nascimento = $_POST['data_nascimento'] ?? '';
        $altura = $_POST['altura'] ?? null;
        $peso = $_POST['peso'] ?? null;
        $sexo = $_POST['sexo'] ?? null;
        $status = $_POST['status'] ?? 'ativo';

        if ($nome === '' || $cpf === '' || $data_nascimento === '') {
            http_response_code(400);
            echo json_encode(['erro' => 'Nome, CPF e data de nascimento são obrigatórios.']);
            return;
        }

        if (!in_array($sexo, ['M', 'F', null], true)) {
            http_response_code(400);
            echo json_encode(['erro' => 'Sexo inválido.']);
            return;
        }

        if (!in_array($status, ['ativo', 'inativo'], true)) {
            http_response_code(400);
            echo json_encode(['erro' => 'Status inválido.']);
            return;
        }

        try {
            $sql = 'INSERT INTO pessoas (nome, cpf, data_nascimento, altura, peso, sexo, status)
                    VALUES (:nome, :cpf, :data_nascimento, :altura, :peso, :sexo, :status)';

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':nome', $nome);
            $stmt->bindValue(':cpf', $cpf);
            $stmt->bindValue(':data_nascimento', $data_nascimento);
            $stmt->bindValue(':altura', $altura);
            $stmt->bindValue(':peso', $peso);
            $stmt->bindValue(':sexo', $sexo);
            $stmt->bindValue(':status', $status);
            $stmt->execute();

            http_response_code(201);
            echo json_encode([
                'mensagem' => 'Pessoa cadastrada com sucesso.',
                'id' => $this->pdo->lastInsertId()
            ], JSON_UNESCAPED_UNICODE);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['erro' => 'Erro ao cadastrar pessoa.']);
        }
    }

    public function atualizar(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $id = filter_input(INPUT_POST, 'id_pessoa', FILTER_VALIDATE_INT);
        $nome = trim($_POST['nome'] ?? '');
        $cpf = trim($_POST['cpf'] ?? '');
        $data_nascimento = $_POST['data_nascimento'] ?? '';
        $altura = $_POST['altura'] ?? null;
        $peso = $_POST['peso'] ?? null;
        $sexo = $_POST['sexo'] ?? null;
        $status = $_POST['status'] ?? 'ativo';

        if (!$id || $nome === '' || $cpf === '' || $data_nascimento === '') {
            http_response_code(400);
            echo json_encode(['erro' => 'ID, nome, CPF e data de nascimento são obrigatórios.']);
            return;
        }

        try {
            $sql = 'UPDATE pessoas
                    SET nome = :nome,
                        cpf = :cpf,
                        data_nascimento = :data_nascimento,
                        altura = :altura,
                        peso = :peso,
                        sexo = :sexo,
                        status = :status
                    WHERE id_pessoa = :id';

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':nome', $nome);
            $stmt->bindValue(':cpf', $cpf);
            $stmt->bindValue(':data_nascimento', $data_nascimento);
            $stmt->bindValue(':altura', $altura);
            $stmt->bindValue(':peso', $peso);
            $stmt->bindValue(':sexo', $sexo);
            $stmt->bindValue(':status', $status);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            echo json_encode(['mensagem' => 'Pessoa atualizada com sucesso.'], JSON_UNESCAPED_UNICODE);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['erro' => 'Erro ao atualizar pessoa.']);
        }
    }

    public function excluir(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $id = filter_input(INPUT_POST, 'id_pessoa', FILTER_VALIDATE_INT);

        if (!$id) {
            http_response_code(400);
            echo json_encode(['erro' => 'ID inválido.']);
            return;
        }

        try {
            $sql = "UPDATE pessoas SET status = 'inativo' WHERE id_pessoa = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() === 0) {
                http_response_code(404);
                echo json_encode(['erro' => 'Pessoa não encontrada.']);
                return;
            }

            echo json_encode(['mensagem' => 'Pessoa inativada com sucesso.']);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['erro' => 'Erro ao inativar pessoa.']);
        }
    }
}




?>


