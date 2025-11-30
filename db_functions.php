<?php
// Ativa erros do MySQL
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

/* ===========================
   ENDEREÇO
=========================== */

// Insere endereço
function insert_endereco(mysqli $conn, $cep, $uf, $cidade, $rua, $complemento)
{
    $stmt = $conn->prepare("INSERT INTO endereco_paciente (cep, uf, cidade, rua, complemento) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $cep, $uf, $cidade, $rua, $complemento);
    $stmt->execute();
    return $stmt->insert_id;
}

// Atualiza endereço
function update_endereco(mysqli $conn, $id_endereco, $cep, $uf, $cidade, $rua, $complemento)
{
    $stmt = $conn->prepare("UPDATE endereco_paciente SET cep=?, uf=?, cidade=?, rua=?, complemento=? WHERE id_endereco=?");
    $stmt->bind_param("sssssi", $cep, $uf, $cidade, $rua, $complemento, $id_endereco);
    return $stmt->execute();
}

// Exclui endereço
function delete_endereco(mysqli $conn, $id_endereco)
{
    $stmt = $conn->prepare("DELETE FROM endereco_paciente WHERE id_endereco=?");
    $stmt->bind_param("i", $id_endereco);
    return $stmt->execute();
}


/* ===========================
   ACOMPANHANTE
=========================== */

// Insere acompanhante
function insert_acompanhante(mysqli $conn, $nome, $idade, $cpf, $telefone)
{
    $stmt = $conn->prepare("INSERT INTO acompanhante (nome, idade, cpf_acompanhante, telefone_acompanhante) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("siss", $nome, $idade, $cpf, $telefone);
    $stmt->execute();
    return $stmt->insert_id;
}

// Atualiza acompanhante
function update_acompanhante(mysqli $conn, $id_acompanhante, $nome, $idade, $cpf, $telefone)
{
    $stmt = $conn->prepare("UPDATE acompanhante SET nome=?, idade=?, cpf_acompanhante=?, telefone_acompanhante=? WHERE id_acompanhante=?");
    $stmt->bind_param("sissi", $nome, $idade, $cpf, $telefone, $id_acompanhante);
    return $stmt->execute();
}

// Exclui acompanhante
function delete_acompanhante(mysqli $conn, $id_acompanhante)
{
    $stmt = $conn->prepare("DELETE FROM acompanhante WHERE id_acompanhante=?");
    $stmt->bind_param("i", $id_acompanhante);
    return $stmt->execute();
}


/* ===========================
   PACIENTE
=========================== */

// Insere paciente
function insert_paciente(mysqli $conn, $nome, $idade, $cpf, $telefone, $email, $senha_hash, $id_endereco, $id_acompanhante)
{
    $stmt = $conn->prepare("
        INSERT INTO pacientes 
        (nome, idade, cpf, telefone, email, senha, id_endereco, id_acompanhante) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->bind_param(
        "sissssii",
        $nome,
        $idade,
        $cpf,
        $telefone,
        $email,
        $senha_hash,
        $id_endereco,
        $id_acompanhante
    );

    $stmt->execute();
    return $stmt->insert_id;
}

// Atualiza paciente (CORRIGIDO)
function update_paciente(mysqli $conn, $id_paciente, $nome, $idade, $cpf, $telefone, $email, $senha_hash, $id_endereco, $id_acompanhante)
{
    $sql = "UPDATE pacientes 
            SET nome=?, idade=?, cpf=?, telefone=?, email=?, id_endereco=?, id_acompanhante=?";

    $types = "sisssii";
    $params = [$nome, $idade, $cpf, $telefone, $email, $id_endereco, $id_acompanhante];

    if ($senha_hash !== null) {
        $sql .= ", senha=?";
        $types .= "s";
        $params[] = $senha_hash;
    }

    $sql .= " WHERE id_paciente=?";
    $types .= "i";
    $params[] = $id_paciente;

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);

    return $stmt->execute();
}

// Exclui paciente
function delete_paciente(mysqli $conn, $id_paciente)
{
    $stmt = $conn->prepare("DELETE FROM pacientes WHERE id_paciente=?");
    $stmt->bind_param("i", $id_paciente);
    return $stmt->execute();
}


/* ===========================
   SELECT GENÉRICO
=========================== */

function fetch_all(mysqli $conn, $table, $order_by, $select_cols = "*")
{
    $table = preg_replace('/[^a-zA-Z0-9_]/', '', $table);
    $order_by = preg_replace('/[^a-zA-Z0-9_]/', '', $order_by);

    $result = $conn->query("SELECT $select_cols FROM $table ORDER BY $order_by ASC");
    return $result->fetch_all(MYSQLI_ASSOC);
}
?>