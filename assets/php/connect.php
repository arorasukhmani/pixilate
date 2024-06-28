<?php
session_start();

// Connect to MySQL
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($mysqli->connect_error) {
    die("MySQL connection failed: " . $mysqli->connect_error);
}

// Connect to MongoDB
$mongoClient = new MongoDB\Client('mongodb://localhost:27017');
$mongoDatabase = 'pixilate';
$mongoCollection = 'pixi1';


$tables = array('block_list', 'comments', 'follow_list','likes','messages','notifications','posts','users'); 
foreach ($tables as $table) {
   
    $sql = "SELECT * FROM $table";
    $result = $mysqli->query($sql);

    if ($result->num_rows > 0) {
        
        $mongoCollection = $table; 
        $mongoColl = $mongoClient->$mongoDatabase->$mongoCollection;

        while ($row = $result->fetch_assoc()) {
     
            $mongoDocument = $mongoColl->findOne(['_id' => $row['id']]);

            if ($mongoDocument) {
         
                $updateResult = $mongoColl->updateOne(['_id' => $row['id']], ['$set' => $row]);
                echo "Updated document in collection '$mongoCollection' with _id: " . $row['id'] . "\n";
            } else {
   
                $insertResult = $mongoColl->insertOne($row);
                echo "Inserted new document in collection '$mongoCollection' with _id: " . $row['id'] . "\n";
            }
        }
    } else {
        echo "No records found in table '$table'.\n";
    }
}

// Close connections
$mysqli->close();
$mongoClient->close();
?>
