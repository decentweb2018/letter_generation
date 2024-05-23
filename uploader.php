<?php 
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Uploader</title>
    
    <style>
    body {
        font-family: Arial, sans-serif;
        margin: 20px;
    }
    .btn {
        display: inline-block;
        padding: 12px 24px;
        border-radius: 3px;
        background-color: #b4d4ef;
        outline: none;
        border: none;
        cursor: pointer;
    }
    a {
        color: #000;
        text-decoration:none;
    }
    label {
        text-transform: uppercase;
        font-size: 15px;
    }
    textarea {
        width: 100%;
        max-width: 700px;
        min-height: 200px;
        font-family: Arial;
        padding: 12px;
    }
    .text_block input {
        width: 100%;
        max-width: 700px;
        font-family: Arial;
        padding: 12px;
    }
    .note {
        font-family: Arial;
        font-size: 14px;
        letter-spacing: 0.01em;
        color: #4d13ff;
    }
    .note b {
        font-weight: 900;
        padding: 5px 9px;
        background-color: aliceblue;
        border-radius: 15px;
    }
    </style>
    
</head>
<body>

<?php

if ( isset($_SESSION["authorized"]) && $_SESSION["authorized"] === '1' && isset($_FILES) && !empty($_FILES) && $_FILES['inputfile']['error'] == 0 ){ // Проверяем, загрузил ли пользователь файл

        /*echo '<pre>';
        print_r($_FILES['inputfile']['error']);
        echo '<hr>';*/
        
        if ( $_FILES['inputfile']['error'] ) {
            echo 'Ошибка: ' . $_FILES['inputfile']['error'];
            exit;
        }

        if( isset( $_POST ) && !empty( $_POST['action'] ) && 'csv_upload' === $_POST['action'] ){

            $mimes = array('text/csv');
            if( in_array( $_FILES['inputfile']['type'], $mimes ) ){
                //echo "The file type is csv.<br>";
            } else {
                echo "Формат файла должен быть csv.<br>";
            }
    
            $destiation_dir = dirname(__FILE__) .'/'.$_FILES['inputfile']['name']; // Директория для размещения файла
            move_uploaded_file($_FILES['inputfile']['tmp_name'], $destiation_dir ); // Перемещаем файл в желаемую директорию

            echo '<p><i>Файл загружен.</i></p>';

            echo '<a href="/create_file/" class="btn">Перейти к созданию файла</a>';
            echo '<br><br><br><hr><br><br>';
            //exit;

        }

}


if( isset( $_POST ) && !empty( $_POST['action'] ) && 'save_messages' === $_POST['action'] ){


    $fn = 'data.json';

    $dataJson = file_get_contents( $fn );
    //Converts to an array
    $dataJson = json_decode( $dataJson, true);

    $dataJson['texts']['1'] = !empty( $_POST['message_1'] ) ? $_POST['message_1'] : '';
    $dataJson['texts']['2'] = !empty( $_POST['message_2'] ) ? $_POST['message_2'] : '';
    $dataJson['texts']['3'] = !empty( $_POST['message_3'] ) ? $_POST['message_3'] : '';

    file_put_contents( $fn, json_encode( $dataJson ) );

    echo '<p><i>Сообщения сохранены.</i></p><br>';
}


if( isset( $_POST ) && !empty( $_POST['action'] ) && 'save_headers' === $_POST['action'] ){


    $fn = 'email-headers.json';

    $headersJson = file_get_contents( $fn );
    //Converts to an array
    $headersJson = json_decode( $headersJson, true);

    $headersJson['headers']['1'] = !empty( $_POST['header_1'] ) ? $_POST['header_1'] : '';
    $headersJson['headers']['2'] = !empty( $_POST['header_2'] ) ? $_POST['header_2'] : '';
    $headersJson['headers']['3'] = !empty( $_POST['header_3'] ) ? $_POST['header_3'] : '';
    $headersJson['headers']['4'] = !empty( $_POST['header_4'] ) ? $_POST['header_4'] : '';

    file_put_contents( $fn, json_encode( $headersJson ) );

    echo '<p><i>Заголовки сохранены.</i></p><br>';
}



if ( isset( $_POST ) && isset( $_POST['pass']) ) {
    if ( $_POST['pass'] === 'RED-apple' ) {

        $_SESSION["authorized"] = '1';

    } else {
        echo "<p>Пароль не подходит.</p><br>";
    }
}

 
if ( isset($_SESSION["authorized"]) && $_SESSION["authorized"] === '1' ) : ?>

    <form method="post" enctype="multipart/form-data">

        <label for="inputfile">Загрузите emails.csv файл</label>

        <br>
        <br>
        
        <div class="note">Название компании должно быть в 1 столбце, email - в 3, имена - в 4.</div>

        <br>
        <br>

        <input type="file" id="inputfile" name="inputfile">
        <br>
        <br>
        <input type="hidden" name="action" value="csv_upload">
        <input type="submit" class="btn" value="Click To Upload">
    </form>

    
    <br>
    <br>
    <hr>
    <br>
    <br>
    
    <div class="note">Вместо <b>%name%</b> в заголовках и сообщениях будет подставлено имя</div>
    
    <br>

    <div class="note">Вместо <b>%x%</b> в заголовках и сообщениях будет подставлено название компании</div>

    <br>
    <br>
    <hr>

    <?php
    $headersJson = file_get_contents( 'email-headers.json' );
    //Converts to an array
    $headersJson = json_decode( $headersJson, true);
    ?>
    
    <form id="email-headers" method="post">

        <br>
        <h2>Заголовки</h2>

        <div class="text_block">
            <p>Заголовок 1</p>
            <input name="header_1" value="<?php echo $headersJson['headers']['1']; ?>">
        </div>

        <div class="text_block">
            <p>Заголовок 2</p>
            <input name="header_2" value="<?php echo $headersJson['headers']['2']; ?>">
        </div>

        <div class="text_block">
            <p>Заголовок 3</p>
            <input name="header_3" value="<?php echo $headersJson['headers']['3']; ?>">
        </div>

        <div class="text_block">
            <p>Заголовок 4</p>
            <input name="header_4" value="<?php echo $headersJson['headers']['4']; ?>">
        </div>

        <br>
        <input type="hidden" name="action" value="save_headers">
        <input type="submit" class="btn" value="Сохранить заголовки">
    </form>

    <br>
    <br>
    <hr>
    <br>

    <?php
    $dataJson = file_get_contents( 'data.json' );
    //Converts to an array
    $dataJson = json_decode( $dataJson, true);
    ?>

    <form id="email-messages" method="post">
        <div class="text_block">
            <p>Сообщение 1</p>
            <textarea name="message_1"><?php echo $dataJson['texts']['1']; ?></textarea>
        </div>
        
        <div class="text_block">
            <p>Сообщение 2</p>
            <textarea name="message_2"><?php echo $dataJson['texts']['2']; ?></textarea>
        </div>
        
        <div class="text_block">
            <p>Сообщение 3</p>
            <textarea name="message_3"><?php echo $dataJson['texts']['3']; ?></textarea>
        </div>

        <br>
        <input type="hidden" name="action" value="save_messages">
        <input type="submit" class="btn" value="Сохранить Сообщения">
    </form>
    
    </br>
    </br>

<?php else: ?>

    <p>Вам необходимо авторизоваться.</p>

    <form method="POST">
        <label for="pass">Введите пароль:</label>
        <br>
        <input type="password" name="pass" id="pass">
        <input type="submit" value="Submit">
        <br>
        <label><input type="checkbox" class="password-checkbox"> Показать пароль</label>
    </form>

    <script>   
    window.addEventListener("DOMContentLoaded", function() {
    
        let passCheckbox = document.querySelector('.password-checkbox')
        let passInput = document.querySelector('#pass')
        passCheckbox.addEventListener('click',  function() {
            if ( this.checked ) {
                passInput.type = 'text'
            } else {
                passInput.type = 'password'
            }
        })
  
    });
    </script>

    <?php 

    exit;

endif; ?>

</body>
</html>