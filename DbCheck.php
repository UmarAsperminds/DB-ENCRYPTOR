<?php
error_reporting(0);
if(isset($_GET['DbCheck'])){
	$conn = new mysqli($_POST['HostName'], $_POST['UserName'], $_POST['Password']);
	if($conn) $connCheck = 1; else $connCheck = 0;
	if($connCheck == 1){
		$query = $conn->query("SHOW DATABASES");
		$string = '';
		while($row = $query->fetch_assoc())
		{
			$string .= "<option>".$row['Database']."</option>";
		}
		echo $string;
	}
	else if($connCheck == 0)
		echo "Error";
}
else if(isset($_GET['Function'])){
	//echo $_POST['HostName']."==".$_POST['UserName']."==".$_POST['Password']."==".$_POST['DbName'];
	$conn = new mysqli($_POST['HostName'], $_POST['UserName'], $_POST['Password'], $_POST['DbName']);
	//echo $_GET['ToDo'];
	$VariableKey = '54K7h1';
	$ColumnNameArray = array();$StoredProcedure = array();
	$TableName = $conn->query("SELECT table_name FROM INFORMATION_SCHEMA.TABLES WHERE table_schema='".$_POST['DbName']."'");
	while($row = $TableName->fetch_assoc())
	{
	    $columns = $conn->query("SELECT CHARACTER_MAXIMUM_LENGTH,DATA_TYPE,COLUMN_NAME,COLUMN_KEY FROM INFORMATION_SCHEMA.COLUMNS WHERE table_schema='".$_POST['DbName']."' and table_name='".$row['table_name']."'");
	    $ColumnChangeQuery = "ALTER TABLE ".$_POST['DbName'].".".$row['table_name'];
	    $ColumnNameQuery = "UPDATE ".$_POST['DbName'].".".$row['table_name']." SET ";
	    $ColumnIntQuery = "UPDATE ".$_POST['DbName'].".".$row['table_name']." SET ";
	    while($columnrow = $columns->fetch_assoc())
	    {
	        array_push($ColumnNameArray, $columnrow['COLUMN_NAME']);
	        $Changer = $columnrow['CHARACTER_MAXIMUM_LENGTH'] == null ? $columnrow['DATA_TYPE'] : $columnrow['DATA_TYPE'].' ('.$columnrow['CHARACTER_MAXIMUM_LENGTH'].')';
	        if($_GET['ToDo'] == 'Encrypt'){
	            $ColumnChangeQuery .= ' CHANGE '.$columnrow['COLUMN_NAME'].' '.str_replace('=',$VariableKey,base64_encode(str_rot13($columnrow['COLUMN_NAME']))).' '.$Changer.',';
	            if($columnrow['DATA_TYPE'] == 'varchar' || $columnrow['DATA_TYPE'] == 'text')
	                $ColumnNameQuery .= $columnrow['COLUMN_NAME']." = DES_ENCRYPT(".$columnrow['COLUMN_NAME'].", '".$VariableKey."') , ";
	            else if($columnrow['DATA_TYPE'] == 'int' && $columnrow['COLUMN_KEY'] != 'PRI')
	            	$ColumnIntQuery .= $columnrow['COLUMN_NAME']." = ".($columnrow['COLUMN_NAME'])."*2 , ";
	        }
	        else if($_GET['ToDo'] == 'Decrypt'){
	            $ColumnChangeQuery .= ' CHANGE '.$columnrow['COLUMN_NAME'].' '.str_rot13(base64_decode(str_replace($VariableKey,'=',$columnrow['COLUMN_NAME']))).' '.$Changer.',';
	            if($columnrow['DATA_TYPE'] == 'varchar' || $columnrow['DATA_TYPE'] == 'text')
	                $ColumnNameQuery .= $columnrow['COLUMN_NAME']." = DES_DECRYPT(".$columnrow['COLUMN_NAME'].", '".$VariableKey."') , ";
	            else if($columnrow['DATA_TYPE'] == 'int' && $columnrow['COLUMN_KEY'] != 'PRI')
	            	$ColumnIntQuery .= $columnrow['COLUMN_NAME']." = ".($columnrow['COLUMN_NAME'])."/2 , ";
	        }
	    }
	    $executor = rtrim($ColumnChangeQuery, ", ").";";
	    $ColumnNameQuery = rtrim($ColumnNameQuery, ", ").";";
	    $ColumnIntQuery = rtrim($ColumnIntQuery, ", ").";";
	    $check = $conn->query($ColumnNameQuery);
	    $check = $conn->query($ColumnIntQuery);
	    //echo $ColumnIntQuery;
	    $check = $conn->query($executor);
	    if($check) $Tempo = 1;
	}
	if($_GET['ToDo'] == 'Encrypt' && $Tempo == 1)
		echo "Success&Database Encryption Successfull..!";
	else if($_GET['ToDo'] == 'Decrypt' && $Tempo == 1)
		echo "Success&Database Decryption Successfull..!";
	else
		echo "Error";
}
?>