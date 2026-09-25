<?php
include('concomodato.php'); // Arquivo contendo a variável com os dados da conexão ao banco de dados
include('consenior.php'); // arquivo de conexao com o banco do senior
include('verificalogin.php');
if( $pdo === false ) {
    die( print_r( sqlsrv_errors(), true));
}
if(isset($_GET['submit'])){
    if(isset($_GET['matricula'])){
        $matricula = $_GET['matricula'];
        $querysenior = "select distinct R.numcad,R.numcpf,R.nomfun, N.NomLoc as NomeLocal, RUA.endrua as 'Rua', RUA.endnum as 'Numero', BAI.NomBai as Bairro, CID.NomCid as Cidade, RUA.endcep as CEP,RUA.endcpl as Complemento
        from REDESUL..R034fun R
        left join REDESUL..R016ORN N on R.numloc = N.NumLoc
        left join REDESUL..R016HIE C on N.NumLoc = c.NumLoc
        left join REDESUL..R006ESC E on R.codesc = E.CodEsc
        left join REDESUL..R034CPL RUA on RUA.numcad = R.numcad
        left join REDESUL..R074CID CID on CID.CodCid = RUA.CodCid
        left join REDESUL..R074BAI BAI on BAI.CodBai = RUA.codbai
        where r.Taborg = 6 AND numcad = '$matricula'";
        $resultsenior = sqlsrv_query($pdo, $querysenior);
        echo $resultsenior;
    }else{
        echo "não deu bom";
    };
    }
?>