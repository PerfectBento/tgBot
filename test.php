<?php
 $result_query = file_get_contents('https://api.l-post.ru/special-for-bot/orders-info?json={"Fields":["StatusInfo","StatusHistory","DeliveryInfo","PickupPointInfo","PaymentInfo","OrderProducts","CustomerInfo"],"CustomerPhones":["9680960330"]}&type=lpost');
 echo $result_query;

?>