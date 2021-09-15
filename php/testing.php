<?php
    require 'Reconoserid.class.php';
    $obj = new Reconoserid();
    $obj->solicitudValidacion("CEDULA_REAL_AQUI");
    $obj->ConsultarValidacion();
    //echo $obj->getToken();

    //Obetener la URL para realizar el procedimiento desde Olimpia
    echo $obj->getUrlProceso();
?>