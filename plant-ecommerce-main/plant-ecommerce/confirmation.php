<?php
session_start();
require "./includes/head.php";
require './includes/conn.php';

if (!isset($_SESSION['email'])) {
  echo "<script> location.href='/ecommerce'; </script>";
  exit();
}
?>

<?php

$user_id = $_SESSION['user_id'];
$query = 'SELECT products.price, products.id, products.title, products.qty AS stock, cart.qty from cart, products where products.id = cart.product_id and cart.user_id="' . $user_id . '"';

$result = mysqli_query($con, $query);

// Calculate totals first
$cart_items = [];
$subtotal = 0;
$total_qty = 0;
while ($row = mysqli_fetch_array($result)) {
  if ($row['qty'] > $row['stock']) {
      echo "<script>alert('Sản phẩm \"".htmlspecialchars($row['title'])."\" chỉ còn {$row['stock']} sản phẩm trong kho. Đơn hàng chưa được tạo!'); window.location='cart.php';</script>";
      exit();
  }
  $cart_items[] = $row;
  $subtotal += $row['price'] * $row['qty'];
  $total_qty += $row['qty'];
}

if (count($cart_items) > 0) {
  // Shipping: 10,000 VND per item
  $shipping = $total_qty * 10000;
  $total_amount = $subtotal + $shipping;

  // Payment Method
  $payment_method = isset($_POST['payment_method']) ? mysqli_real_escape_string($con, $_POST['payment_method']) : 'Tiền mặt';

  // Contact and Address Info
  $first_name = isset($_POST['first_name']) ? mysqli_real_escape_string($con, $_POST['first_name']) : '';
  $last_name  = isset($_POST['last_name']) ? mysqli_real_escape_string($con, $_POST['last_name']) : '';
  $mobile     = isset($_POST['mobile']) ? mysqli_real_escape_string($con, $_POST['mobile']) : '';
  
  $street     = isset($_POST['street']) ? mysqli_real_escape_string($con, $_POST['street']) : '';
  $ward       = isset($_POST['ward']) ? mysqli_real_escape_string($con, $_POST['ward']) : '';
  $district   = isset($_POST['district']) ? mysqli_real_escape_string($con, $_POST['district']) : '';
  
  $address_parts = array_filter([$street, $ward, $district]);
  $address = implode(', ', $address_parts);

  // Update latest info to user profile
  $update_user_sql = "UPDATE `users` SET 
                        `first_name` = '$first_name', 
                        `last_name` = '$last_name', 
                        `mobile` = '$mobile', 
                        `address` = '$address' 
                      WHERE `id` = $user_id";
  mysqli_query($con, $update_user_sql);

  // 1. Create ONE order
  $order_sql = "INSERT INTO `orders`(`user_id`, `product_id`, `product_qty`, `order_amount`, `shipping_fee`, `status`, `payment_method`) 
    VALUES ($user_id, " . $cart_items[0]['id'] . ", $total_qty, $total_amount, $shipping, 'confirmed', '$payment_method')";
  mysqli_query($con, $order_sql);
  $order_id = mysqli_insert_id($con);

  // 2. Insert each product as an order_item
  foreach ($cart_items as $item) {
    $pid = $item['id'];
    $qty = $item['qty'];
    $price = $item['price'];
    $item_sql = "INSERT INTO `order_items`(`order_id`, `product_id`, `quantity`, `unit_price`) 
      VALUES ($order_id, $pid, $qty, $price)";
    mysqli_query($con, $item_sql);

    // Decrease stock by actual quantity
    $decrease_sql = "UPDATE products SET qty = qty - $qty WHERE id = $pid";
    mysqli_query($con, $decrease_sql);
  }

  // 3. Clear cart
  $delete_cart = "DELETE FROM cart WHERE user_id = '$user_id'";
  mysqli_query($con, $delete_cart);
}

?>



<section class="breadcrumb breadcrumb_bg">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-8">
        <div class="breadcrumb_iner">
          <div class="breadcrumb_iner_item">
            <h2>Order Confirmation</h2>
            <p>Home <span>-</span> Order Confirmation</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>


<section class="confirmation_part padding_top">
  <div class="container">
    <div class="row">
      <div class="col-lg-12">
        <div class="confirmation_tittle">
          <h1><span>Thank you. Your order has been received.</span></h1>
        </div>
      </div>
      <!-- <div class="col-lg-6 col-lx-4">
        <div class="single_confirmation_details">
          <h4>order info</h4>
          <ul>
            <li>
              <p>order number</p><span>: 60235</span>
            </li>
            <li>
              <p>data</p><span>: Oct 03, 2017</span>
            </li>
            <li>
              <p>total</p><span>: USD 2210</span>
            </li>
            <li>
              <p>mayment methord</p><span>: Check payments</span>
            </li>
          </ul>
        </div>
      </div>
      <div class="col-lg-6 col-lx-4">
        <div class="single_confirmation_details">
          <h4>Billing Address</h4>
          <ul>
            <li>
              <p>Street</p><span>: 56/8</span>
            </li>
            <li>
              <p>city</p><span>: Los Angeles</span>
            </li>
            <li>
              <p>country</p><span>: United States</span>
            </li>
            <li>
              <p>postcode</p><span>: 36952</span>
            </li>
          </ul>
        </div>
      </div>
      <div class="col-lg-6 col-lx-4">
        <div class="single_confirmation_details">
          <h4>shipping Address</h4>
          <ul>
            <li>
              <p>Street</p><span>: 56/8</span>
            </li>
            <li>
              <p>city</p><span>: Los Angeles</span>
            </li>
            <li>
              <p>country</p><span>: United States</span>
            </li>
            <li>
              <p>postcode</p><span>: 36952</span>
            </li>
          </ul>
        </div>
      </div> -->
    </div>
    <!-- <div class="row">
      <div class="col-lg-12">
        <div class="order_details_iner">
          <h3>My Order</h3>
          <br><br>
          <table class="table table-borderless">
            <thead>
              <tr>
                <th scope="col" colspan="2">Product</th>
                <th scope="col">Quantity</th>
                <th scope="col">Total</th>
                <th scope="col">Status</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $allOrders = "SELECT * from orders, products where user_id='$user_id' and orders.product_id=products.id";
              $orderresult = mysqli_query($con, $allOrders);
              while($row = mysqli_fetch_array($orderresult)){
                echo '<tr>
                        <th colspan="2"><span>'.$row['product_id'].'</span></th>
                        <th>'.$row['product_qty'].'</th>
                        <th> <span>'.$row['order_amount'].'</span></th>
                        <th> <span>'.$row['status'].'</span></th>
                      </tr>';
              }
              
              ?>
          </table>
        </div>
      </div>
    </div> -->
  </div>
</section>


<?php require './includes/footer.php' ?>



<script src="js/jquery-1.12.1.min.js"></script>

<script src="js/popper.min.js"></script>

<script src="js/bootstrap.min.js"></script>

<script src="js/jquery.magnific-popup.js"></script>

<script src="js/swiper.min.js"></script>

<script src="js/masonry.pkgd.js"></script>

<script src="js/owl.carousel.min.js"></script>
<script src="js/jquery.nice-select.min.js"></script>

<script src="js/slick.min.js"></script>
<script src="js/jquery.counterup.min.js"></script>
<script src="js/waypoints.min.js"></script>
<script src="js/contact.js"></script>
<script src="js/jquery.ajaxchimp.min.js"></script>
<script src="js/jquery.form.js"></script>
<script src="js/jquery.validate.min.js"></script>
<script src="js/mail-script.js"></script>
<script src="js/stellar.js"></script>
<script src="js/price_rangs.js"></script>

<script src="js/custom.js"></script>

<script async src="https://www.googletagmanager.com/gtag/js?id=UA-23581568-13"></script>
<script>
  window.dataLayer = window.dataLayer || [];

  function gtag() {
    dataLayer.push(arguments);
  }
  gtag('js', new Date());

  gtag('config', 'UA-23581568-13');
</script>
<script defer src="https://static.cloudflareinsights.com/beacon.min.js/vaafb692b2aea4879b33c060e79fe94621666317369993" integrity="sha512-0ahDYl866UMhKuYcW078ScMalXqtFJggm7TmlUtp0UlD4eQk0Ixfnm5ykXKvGJNFjLMoortdseTfsRT8oCfgGA==" data-cf-beacon='{"rayId":"7721ac24fb7b3390","token":"cd0b4b3a733644fc843ef0b185f98241","version":"2022.11.3","si":100}' crossorigin="anonymous"></script>
</body>

</html>

</html>

</html>

</html>

</html>

</html>