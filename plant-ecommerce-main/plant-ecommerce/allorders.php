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
// $query = 'SELECT products.price, products.id, products.title, products.image, cart.qty from cart, products where products.id = cart.product_id and cart.user_id="' . $user_id . '"';

// $result = mysqli_query($con, $query);

// while ($row = mysqli_fetch_array($result)) {
//   $order = "INSERT INTO `orders`(`product_id`, `user_id`, `product_qty`, `order_amount`, `status`) 
//     VALUES (" . $row['id'] . "," . $user_id . "," . $row['qty'] . "," . $row['price'] * $row['qty'] + 49 . ", 'Confirmed')";

//   $answer = mysqli_query($con, $order);
// }

?>



<section class="breadcrumb breadcrumb_bg">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-8">
        <div class="breadcrumb_iner">
          <div class="breadcrumb_iner_item">
            <h2>My Orders</h2>
            <p>Home <span>-</span> All Orders</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>


<section class="confirmation_part padding_top">
  <div class="container">
    <!-- <div class="row">
      <div class="col-lg-6 col-lx-4">
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
      </div>
    </div> -->
    <div class="row">
      <div class="col-lg-12">
        <div class="order_details_iner">
          <h3>Đơn hàng của tôi</h3>
          <br><br>
          <table class="table table-borderless">
<thead>
<tr>
  <th scope="col">Mã đơn</th>
  <th scope="col">Thời gian đặt</th>
  <th scope="col">Sản phẩm</th>
  <th scope="col">Số lượng</th>
  <th scope="col">Tổng</th>
  <th scope="col">Trạng thái</th>
  <th scope="col">Chi tiết</th>
</tr>
</thead>
            <tbody>
              <?php
              $allOrders = "SELECT o.id, o.order_date, o.product_qty, o.order_amount, o.status,
                GROUP_CONCAT(p.title SEPARATOR ', ') as product_names,
                COUNT(oi.id) as item_count
                FROM orders o 
                LEFT JOIN order_items oi ON oi.order_id = o.id
                LEFT JOIN products p ON oi.product_id = p.id
                WHERE o.user_id='$user_id'
                GROUP BY o.id
                ORDER BY o.id DESC";
              $orderresult = mysqli_query($con, $allOrders);
             while($row = mysqli_fetch_array($orderresult)){
                $product_display = $row['product_names'] ?? 'N/A';
                if ($row['item_count'] > 1) {
                    $product_display = mb_substr($product_display, 0, 40) . '... (' . $row['item_count'] . ' SP)';
                }
echo '<tr>
        <th><span>'.$row['id'].'</span></th>
        <th><span>'.$row['order_date'].'</span></th>
        <th><span>'.htmlspecialchars($product_display).'</span></th>
        <th>'.$row['product_qty'].'</th>
        <th><span>'.number_format($row['order_amount'], 0, ',', '.').' VNĐ</span></th>
        <th><span>'.([
            'pending' => 'Chưa xử lý',
            'confirmed' => 'Đã xác nhận',
            'Confirmed' => 'Đã xác nhận',
            'shipped' => 'Đã giao',
            'delivered' => 'Đã giao',
            'cancelled' => 'Đã hủy',
        ][$row['status']] ?? $row['status']).'</span></th>
        <td>
            <a href="order_detail.php?id='.$row['id'].'" class="btn btn-sm btn-primary">
            View
            </a>
        </td>
      </tr>';
}
              
              ?>
          </table>
        </div>
      </div>
    </div>
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