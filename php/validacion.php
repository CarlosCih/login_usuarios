<?php
// Conectar a la base de datos
$conn = new mysqli("localhost", "root", "", "login");//cambiar la contraseña de la base de datos si no es necesario
// Verificar si la conexión fue exitosa
if ($conn->connect_errno) {
    echo "No hay conexión: (" . $conn->connect_errno . ")" . $conn->connect_errno;
}

// Función para cifrar la contraseña
function cifrarContrasena($contrasena)
{
    return md5($contrasena);
}

if($conn->connect_error){
    die("Conexión fallida: ".$conn->connect_error);
}

// Verificar si se han enviado los datos del formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['registro'])) {
        //se verifica si se enviaron los datos
        if (isset($_POST['nombres']) && isset($_POST['apellidos']) && isset($_POST['usuario']) && isset($_POST['email']) && isset($_POST['password'])) {
            $nombres = $_POST['nombres'];
            $apellidos = $_POST['apellidos'];
            $usuario = $_POST['usuario'];
            $correo = $_POST['email'];
            $contrasena = $_POST['password'];

            // Cifrar la contraseña con md5
            $contrasenaCifrada = md5($contrasena);

            //Consulta de insersion en la base de datos
            $sql = "INSERT INTO usuarios (nombres, apellidos, usuario, correo, contrasena) VALUES (?, ?, ?, ?, ?)";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssss", $nombres, $apellidos, $usuario, $correo, $contrasenaCifrada);

            if ($stmt->execute()) {
                echo "<script>alert('Usuario registrado correctamente.');</script>";
                echo "<script>window.location='../index.html'</script>";
            } else {
                echo "<script>alert('Error al registrar usuario: " . $stmt->error . "');</script>";
                echo "<script>window.location='../index.html'</script>";
            }

            $stmt->close();
        } else {
            echo "Error en el formulario";
        }
    } else if (isset($_POST['login'])) {
        // Procesar el formulario de inicio de sesión
        $usuario_l = $_POST['usuario'];
        $contrasena_l= $_POST['contrasena'];

        // Obtener la contraseña cifrada de la base de datos
        $sql = "SELECT contrasena FROM usuarios WHERE usuario=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $usuario_l);
        $stmt->execute();
        $result = $stmt->get_result();

        $row = mysqli_fetch_assoc($result);

        if ($row !== null) {
            session_start();
            error_reporting();
            $contrasena_cifrada_bd = $row["contrasena"];
            // Generar un identificador de sesión único si no está presente
            if (!isset($_SESSION['session_id'])) {
                $_SESSION['session_id'] = uniqid();
            }
            //Datos necesarios
            $_SESSION['usuario'] = $usuario_l;

            // Verificar la contraseña
            if (cifrarContrasena($contrasena_l) == $contrasena_cifrada_bd) {
                echo "<script>window.location='dashboard.php'</script>";
            } else {
                echo "<script>alert('Usuario o contraseña incorrecta');window.location='../index.html'</script>";
                exit();
            }
        } else {
            echo "Usuario no encontrado.";
        }

        $stmt->close();
    }
}

?>