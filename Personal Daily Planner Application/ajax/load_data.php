<?php 

include("../connection.php");

if (isset($_POST['page'])) {
	$page = $_POST['page'];
}else{
	$page = 0;
}

$pagination = "";


$limit = 30;
$start = ($page - 1)* $page;

$pages = mysqli_query($connect,"SELECT count(id) AS id FROM cart");

while ($row = mysqli_fetch_array($pages)) {
	$total = $row['id'];
	$count = ceil($total / $limit);

   

}




$query = "SELECT * FROM cart LIMIT $start, $limit";
$res = mysqli_query($connect,$query);

$output = "";
if (mysqli_num_rows($res) < 1) {
	$output .= "<h1 class='text-center'>NO DATA IN THE DB</h1>";
}else{

	while ($row = mysqli_fetch_array($res)) {
		 
		 $output .= "
				<style>
				img {
				  display: block;
				  margin-left: auto;
				  margin-right: auto;
				}
				.quantity {
				  display: block;
				  margin-left: auto;
				  margin-right: auto;
				}
				</style>
             <div class='col-md-3 shadow-sm rounded bg-white d-flex justify-content-center'>
				<form method='post'>
					<img src='img/".$row['image']."' class='col-md-12' height='200px'  style='width:50%'>
					<h3 class='mx-3 text-center'>".$row['name']."</h3>
					<h3 class='mx-3 text-center'>RM".$row['price']."</h3>
					
					<input type='hidden' name='id' value='".$row['id']."' id='".$row['id']."'>
					<input type='hidden' name='name' value='".$row['name']."' id='name".$row['id']."'>
					<input type='hidden' name='price' value='".$row['price']."' id='price".$row['id']."'>
					<input type='number' name='quantity' class = 'quantity' value='1' id='quantity".$row['id']."'>
					<input type='submit' name='add' id='".$row['id']."' class='btn btn-warning my-2 add_cart' value='Add To Cart' style='margin-left: 100px;'>
				</form>
				</div>

		 ";
	}
}




$data['output'] = $output;
$data['pagination'] = $pagination;


echo json_encode($data);


 ?>