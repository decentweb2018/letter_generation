<?php

require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

session_start(); 
 
 
if ( isset( $_POST['start_script'] ) && ( !$_POST['start_script'] || $_POST['start_script'] !== 'yes' ) ) : ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create file</title>
    
    <style>
    a {
        text-decoration: none;
    }
    </style>
</head>
<body>

<?php endif;



if ( isset( $_POST['pass'] ) && $_POST['pass'] === 'RED-apple' ) {
    
    $_SESSION["authorized"] = '1';
    
} else if ( isset( $_POST['pass'] ) && $_POST['pass'] !== 'RED-apple' ) {
    echo "<p>Пароль не подходит.</p><br>";
}
    
 
if ( !isset($_SESSION["authorized"]) || ( isset($_SESSION["authorized"]) && $_SESSION["authorized"] !== '1' ) ) { ?>

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
}

 
if ( isset($_SESSION["authorized"]) && $_SESSION["authorized"] === '1' ) :


    if ( !isset( $_POST['start_script'] ) ) : ?>

        <p>Запустите скрипт.</p>

        <form method="post">
            <input type="hidden" name="start_script" value="yes">
            <input type="submit" value="Сформировать csv файл">
        </form>

    <?php 
    endif;




    //Email messages
    $dataJson = file_get_contents( 'data.json' );
    //Converts to an array
    $dataJson = json_decode( $dataJson, true);

    $messages = $dataJson['texts'];

    //Email headers
    $headersJson = file_get_contents( 'email-headers.json' );
    //Converts to an array
    $headersJson = json_decode( $headersJson, true);
    $headersArray = $headersJson['headers'];



    if ( isset($_POST['start_script']) && $_POST['start_script'] === 'yes'  ) :


        if (($handle = fopen("emails.csv", "r")) !== FALSE) {


            $emailsFilename = 'mailing__'. date("d-m-Y_h-i-s") .'.xlsx';

            $spreadsheet = new Spreadsheet();
    

            $limit = 51;

            $k = 0;

            $headers_i = 1;

            $messageInx = 1;

            $sheetInx = 1;

            $worksheetInx = 0;
    

            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {

                
                if ( $k === 0 ) {
                    $k++;
                    continue;
                }
                

                if ( $k === $limit ) {

                    $spreadsheet->createSheet();

                    $worksheetInx += 1;
                    
                    $k = 1;
                    
                    $sheetInx = 1;
                }

                /*
                $headersArray = array(
                    $companyName .' - New Business Enquiry',
                    $companyName .' New Request',
                    'Сollaboration with '. $companyName,
                    'Re: Partnership with '. $companyName
                );
                */
                
                
                //Company Email
                if ( strpos($data[2], 'http') === false ) {
                    $companyEmail = $data[2];
                } else {
                    $companyEmail = '';
                }


                $curr_header = $headersArray[$headers_i];

                $message = $messages[$messageInx];

                
    
                //Company Name
                if( !empty( $data[0] ) ){
                    $companyName = $data[0];
                    
                    $curr_header = str_replace("%x%", $companyName, $curr_header);

                    $message = str_replace("%x%", $companyName, $message);

                } else {
                    $curr_header = str_replace("%x%", '', $curr_header);

                    $message = str_replace("%x%", '', $message);
                }
                

                //Name
                if( !empty( $data[3] ) ){
                    $curr_header = str_replace("%name%", $data[3], $curr_header);

                    $message = str_replace("%name%", $data[3], $message);

                } else {
                    $curr_header = str_replace("%name%", '', $curr_header);

                    $message = str_replace("%name%", '', $message);
                }
                

                $spreadsheet->setActiveSheetIndex($worksheetInx)->setCellValue("A{$sheetInx}", $companyEmail );
                $spreadsheet->setActiveSheetIndex($worksheetInx)->setCellValue("B{$sheetInx}", $curr_header );
                $spreadsheet->setActiveSheetIndex($worksheetInx)->setCellValue("C{$sheetInx}", $message );
                
                
                if ( $headers_i >= 4 ) {
                    $headers_i = 1;
                } else {
                    $headers_i++;
                }
                
                if ( $messageInx >= 3 ) {
                    $messageInx = 1;
                } else {
                    $messageInx++;
                }

                $k++;

                $sheetInx++;
                
                    
            }
            fclose($handle);

            unset($_POST['start_script']);

            $writer = new Xlsx($spreadsheet);
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="'. urlencode($emailsFilename).'"');
            $writer->save('php://output');
            //$writer->save($emailsFilename);

            exit;

        } else {
            echo "No emails.csv found\n";
            exit;
        }

        unset($_POST['start_script']);

        exit;

    endif;

endif;