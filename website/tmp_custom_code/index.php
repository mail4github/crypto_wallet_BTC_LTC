<?php
require('../../includes/application_top.php');
require_once(DIR_WS_CLASSES.'user.class.php');

function get_rates_data($history_arr, $max_number_of_items, $interval_in_minutes, $history_file_name = '', $date_format = 'H:i')
{
    if (!empty($history_file_name)) {
        $t = file_get_contents($history_file_name);
        $history_arr = [];
        if (!empty($t)) {
            $history_arr = json_decode($t, 1);
        }
    }
    $last_moment = 0;
    $last_rate = 0;
    $rate_min = 0;
    $rate_max = 0;
    $time = '';
    $values = '';

    $number_of_items = 0;

    foreach ($history_arr as $history_item) {
        if ($last_moment == 0) {
            $last_moment = $history_item['time'];
            $last_rate = $history_item['rate'];
            $rate_min = $history_item['rate'];
            $rate_max = $history_item['rate'];
        }
        if ( $history_item['time'] <= $last_moment - $number_of_items * 60 * $interval_in_minutes ) {
            $time = date("'$date_format'", $history_item['time']).(empty($time) ? '' : ', ').$time;
            $values = $history_item['rate'].(empty($values) ? '' : ', ').$values;

            if ($rate_min > $history_item['rate']) {
                $rate_min = $history_item['rate'];
            }

            if ($rate_max < $history_item['rate']) {
                $rate_max = $history_item['rate'];
            }
            $number_of_items++;
        }
        if ($number_of_items >= $max_number_of_items) {
            break;
        }
    }
    $rate_min = $rate_min * 0.999;
    $rate_max = $rate_max * 1.0001;

    return [
        'last_rate' => floatval($last_rate),
        'time' => $time, 
        'values' => $values, 
        'rate_min' => $rate_min, 
        'rate_max' => $rate_max
    ];
}

$page_header = 'Dashboard';
$page_title = $page_header;
$page_desc = $page_header;
require(DIR_WS_INCLUDES.'header.php');

$BTC_HIST_FILE_NAME = DIR_WS_TEMP_ON_WEBSITE.'btc_history.json';

$t = file_get_contents($BTC_HIST_FILE_NAME);
$btc_history_arr = [];
if (!empty($t)) {
    $btc_history_arr = json_decode($t, 1);
}
//foreach ($btc_history_arr as $hst) {
//    echo date('m-d-Y H:i:s', $hst['time'])."<br>\r\n";
//}
//exit;
$btc_rates_data_arr = [];
$btc_rates_data_arr['1h'] = get_rates_data($btc_history_arr, 11, 5);
$btc_rates_data_arr['3h'] = get_rates_data($btc_history_arr, 12, 15);
$btc_rates_data_arr['1d'] = get_rates_data($btc_history_arr, 12, 60 * 2);
$btc_rates_data_arr['1w'] = get_rates_data($btc_history_arr, 14, 60 * 12, '', 'M j');
$btc_rates_data_arr['1m'] = get_rates_data($btc_history_arr, 15, 60 * 24 * 2, '', 'M j');

?>
<div class="col-12 d-flex">
    <div class="col-6 d-flex flex-column align-items-center justify-content-between">
        <div class="content-block light-grey mb-3 pb-2 position-relative">
            <div class="block-content d-flex justify-content-between">
                <div class="text-left">
                    <p class="small font-weight-bold mb-0 notranslate"><span class="btc_plain_value"></span> BTC</p>
                    <p class="smaller text-muted mb-1"><?php echo make_str_translateable('Deposit'); ?></p>
                    <p class="large mb-1 available_in_usd_plain_btc notranslate"></p>
                    <p class="small text-success mb-0 notranslate">+0.25%</p>
                </div>
                <canvas class="mt-auto" id="chart1"></canvas>
            </div>
            <i class="bi bi-arrow-down-left-circle-fill text-success position-absolute" style="top:5px; right: 25px; transform: rotate(180deg);  font-size: 24px;"></i>
        </div>
        <div class="content-block dark-grey mb-3 pb-2  position-relative">
            <div class="block-content d-flex justify-content-between">
                <div class="text-left">
                    <p class="small font-weight-bold mb-0 notranslate">0 BTC</p>
                    <p class="smaller text-muted mb-1"><?php echo make_str_translateable('Dynamic balance'); ?></p>
                    <p class="large mb-1 notranslate">$0,00</p>
                    <p class="small text-danger mb-0 notranslate">-0.00%</p>
                </div>
                <canvas class="mt-auto" id="chart2"></canvas>
            </div>
            <i class="bi bi-arrow-down-left-circle-fill text-danger position-absolute" style="top:5px; right: 25px; font-size: 24px;"></i>
        </div>
        <div class="content-block light-grey mb-3 pb-2 position-relative">
            <div class="block-content d-flex justify-content-between">
                <div class="text-left">
                    <p class="small font-weight-bold mb-0 notranslate">0.00003245 BTC</p>
                    <p class="smaller text-muted mb-1"><?php echo make_str_translateable('Deposit'); ?></p>
                    <p class="large mb-1 notranslate">$52,291</p>
                    <p class="small text-success mb-0 notranslate">+0.25%</p>
                </div>
                <canvas class="mt-auto" id="chart3"></canvas>
            </div>
            <i class="bi bi-arrow-down-left-circle-fill text-success position-absolute" style="top:5px; right: 25px; transform: rotate(180deg); font-size: 24px;"></i>
        </div>
    </div>
    <div class="col-6" >
        <div class="content-block pb-2 dark-grey" style="height: 95%;">
            <div class="graph-header">
                <h1><?php echo make_str_translateable('Fixed balance'); ?></h1>
            </div>
            <div class = "d-flex justify-content-between align-items-center mobile-correct">
                <div class="d-flex flex-column">
                <p class="mb-0 mt-2 text-muted small notranslate">Bitcoin/BTC</p>
                <h2 class="notranslate">$<?php echo number_format($btc_rates_data_arr['1h']['last_rate']); ?></h2>
                </div>
                <div class="time-selector">
                <button id="btn-chart-1h" class="btc-chart-change-period active notranslate" onclick="update_chart('1h')">1h</button>
                <button id="btn-chart-3h" class="btc-chart-change-period notranslate" onclick="update_chart('3h')">3h</button>
                <button id="btn-chart-1d" class="btc-chart-change-period notranslate" onclick="update_chart('1d')">1d</button>
                <button id="btn-chart-1w" class="btc-chart-change-period notranslate" onclick="update_chart('1w')">1w</button>
                <button id="btn-chart-1m" class="btc-chart-change-period notranslate" onclick="update_chart('1m')">1m</button>
                </div>
            </div>
            <canvas id="cryptoChart"></canvas>
        </div>
    
    </div>
</div>
<div class="col-12 d-flex margin-correct">
    <?php require_once('transactions_box.inc.php'); ?>
    <div class="col-6">
        <div class="content-block dark-grey">
            <table class="table table-borderless text-white">
                <tbody>
                    <tr>
                        <td class="align-middle pr-0 py-1" style="width: 55px;">
                            <img src="/tmp_custom_code/images/crypto/icon1.png" alt="Icon" width="44" class="table-icon">
                        </td>
                        <td class="align-middle pl-0 py-1">
                            <p class="small mb-0 mt-2 notranslate">Ethereum</p>
                            <p class="smaller text-muted mb-0 notranslate"><?php echo currency_format(get_rates_data([], 1, 1, DIR_WS_TEMP_ON_WEBSITE.'eth_history.json')['last_rate']); ?></p>
                        </td>
                        
                        <td class="align-middle text-right py-1">
                            <div class="d-flex flex-column text-right">
                                <p class="smaller text-danger mb-0 notranslate">-13.40%</p>
                                <p class="text-white small mb-0 notranslate">0 ETH</p>
                            </div>
                        </td>
                        <td class="width-correct py-1">
                            <canvas class="graf no-mobile" id="chart-ethereum" height="34px"></canvas>
                        </td>
                    </tr>
                
                    <tr>
                        <td class="align-middle pr-0 py-1" style="width: 55px;">
                            <img src="/tmp_custom_code/images/crypto/icon2.png" alt="Icon" width="44" class="table-icon">
                        </td>
                        <td class="align-middle pl-0 py-1">
                            <p class="small mb-0 notranslate">Bitcoin</p>
                            <p class="smaller text-muted mb-0 notranslate"><?php echo currency_format(get_rates_data([], 1, 1, DIR_WS_TEMP_ON_WEBSITE.'btc_history.json')['last_rate']); ?></p>
                        </td>
                        
                        <td class="align-middle text-right py-1">
                            <div class="d-flex flex-column text-right">
                            <p class="smaller text-danger mb-0 notranslate">-13.40%</p>
                            <p class="text-white small mb-0 notranslate"><span class="btc_plain_value"></span> BTC</p>
                            </div>
                        </td>
                        <td class="width-correct py-1">
                            <canvas class="graf no-mobile" id="chart-bitcoin" height="34px"></canvas>
                        </td>
                    </tr>
                    
                    <tr>
                        <td class="align-middle pr-0 py-1" style="width: 55px;">
                            <img src="/tmp_custom_code/images/crypto/icon3.png" alt="Icon" width="44" class="table-icon">
                        </td>
                        <td class="align-middle pl-0 py-1">
                            <p class="small mb-0 notranslate">Litecoin</p>
                            <p class="smaller text-muted mb-0 notranslate"><?php echo currency_format(get_rates_data([], 1, 1, DIR_WS_TEMP_ON_WEBSITE.'ltc_history.json')['last_rate']); ?></p>
                        </td>
                        
                        <td class="align-middle text-right  py-1">
                            <div class="d-flex flex-column text-right">
                            <p class="smaller text-success mb-0 notranslate">+14.25%</p>
                            <p class="text-white small mb-0 notranslate"><span class="ltc_plain_value"></span> LTC</p>
                            </div>
                        </td>
                        <td class="width-correct py-1">
                            <canvas class="graf no-mobile" id="chart-litecoin" height="34px"></canvas>
                        </td>
                    </tr>
                    <tr>
                        <td class="align-middle pr-0 py-1" style="width: 55px;">
                            <img src="/tmp_custom_code/images/crypto/icon4.png" alt="Icon" width="44" class="table-icon">
                        </td>
                        <td class="align-middle pl-0 py-1">
                            <p class="small mb-0 notranslate">Solana</p>
                            <p class="smaller text-muted mb-0 notranslate"><?php echo currency_format(get_rates_data([], 1, 1, DIR_WS_TEMP_ON_WEBSITE.'sol_history.json')['last_rate']); ?></p>
                        </td>
                        
                        <td class="align-middle text-right  py-1">
                            <div class="d-flex flex-column text-right">
                            <p class="smaller text-danger mb-0 notranslate">-13.40%</p>
                            <p class="text-white small mb-0 notranslate">0 SOL</p>
                            </div>
                        </td>
                        <td class="width-correct py-1">
                            <canvas class="graf no-mobile" id="chart-solana" height="34px"></canvas>
                        </td>
                    </tr>
                    <tr>
                        <td class="align-middle pr-0 py-1" style="width: 55px;">
                            <img src="/tmp_custom_code/images/crypto/icon5.png" alt="Icon" width="44" class="table-icon">
                        </td>
                        <td class="align-middle pl-0 py-1">
                            <p class="small mb-0 notranslate">Binance Coin</p>
                            <p class="smaller text-muted mb-0 notranslate"><?php echo currency_format(get_rates_data([], 1, 1, DIR_WS_TEMP_ON_WEBSITE.'bnb_history.json')['last_rate']); ?></p>
                        </td>
                        
                        <td class="align-middle text-right  py-1">
                            <div class="d-flex flex-column text-right">
                            <p class="smaller text-success mb-0 notranslate">+12.00%</p>
                            <p class="text-white small mb-0 notranslate">0 BNB</p>
                            </div>
                        </td>
                        <td class="width-correct py-1">
                            <canvas class="graf no-mobile" id="chart-binance" height="34px"></canvas>
                        </td>
                    </tr>
                    <tr>
                        <td class="align-middle pr-0 py-1" style="width: 55px;">
                            <img src="/tmp_custom_code/images/crypto/icon6.png" alt="Icon" width="44" class="table-icon">
                        </td>
                        <td class="align-middle pl-0 py-1">
                            <p class="small mb-0 notranslate">Ripple</p>
                            <p class="smaller text-muted mb-0 notranslate"><?php echo currency_format(get_rates_data([], 1, 1, DIR_WS_TEMP_ON_WEBSITE.'xrp_history.json')['last_rate']); ?></p>
                        </td>
                        
                        <td class="align-middle text-right  py-1">
                            <div class="d-flex flex-column text-right">
                            <p class="smaller text-success mb-0 notranslate">+13.40%</p>
                            <p class="text-white small mb-0 notranslate">0 XRP</p>
                            </div>
                        </td>
                        <td class="width-correct py-1">
                            <canvas class="graf no-mobile" id="chart-ripple" height="34px"></canvas>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
//graph 

const ctx = document.getElementById('cryptoChart').getContext('2d');

// Create gradient
const gradient = ctx.createLinearGradient(0, 0, 0, 200);
gradient.addColorStop(0, 'rgba(122, 194, 49, 0.4)');
gradient.addColorStop(1, 'rgba(122, 194, 49, 0)');

// Функция для обновления оси X в зависимости от ширины экрана
function updateChartForMobile(chart) {
  if (window.innerWidth <= 768) { // Условие для мобильной версии
    chart.options.scales.x.display = false; // Отключить ось X
  } else {
    chart.options.scales.x.display = true; // Включить ось X для больших экранов
  }
  chart.update(); // Применить изменения
}

function spawn_btc_chart(time_arr, data_arr, rate_min, rate_max)
{
    return new Chart(ctx, {
            type: 'line',
                data: {
            labels: time_arr,
            datasets: [
            {
                label: 'Bitcoin Price',
                data: data_arr,
                borderColor: '#7ac231',
                borderWidth: 2,
                fill: true, 
                backgroundColor: gradient, 
                tension: 0, 
                pointRadius: 0, 
            }
            ],
        },
        options: {
            responsive: true,
            plugins: {
                tooltip: {
                callbacks: {
                    label: function (context) {
                    const value = context.raw;
                    return ``;
                    },
                },
                },
            },
            scales: {
                x: {
                grid: {
                    color: '#2a2a2a',
                },
                ticks: {
                    color: '#ffffff',
                },
                },
                y: {
                    grid: {
                        color: '#2a2a2a',
                    },
                    ticks: {
                        color: '#ffffff',
                    },
                    min: rate_min,
                    max: rate_max,
                },
            },
        },
    });
}

function update_chart(chart_name)
{
    cryptoChart.destroy();
    $(".btc-chart-change-period").removeClass("active");
    
    if (chart_name == "1h") {
        cryptoChart = spawn_btc_chart(
            [<?php echo $btc_rates_data_arr['1h']['time']; ?>],
            [<?php echo $btc_rates_data_arr['1h']['values']; ?>], 
            <?php echo $btc_rates_data_arr['1h']['rate_min']; ?>,
            <?php echo $btc_rates_data_arr['1h']['rate_max']; ?>
        );
    }
    else
    if (chart_name == "3h") {
        cryptoChart = spawn_btc_chart(
            [<?php echo $btc_rates_data_arr['3h']['time']; ?>],
            [<?php echo $btc_rates_data_arr['3h']['values']; ?>], 
            <?php echo $btc_rates_data_arr['3h']['rate_min']; ?>,
            <?php echo $btc_rates_data_arr['3h']['rate_max']; ?>
        );
    }
    else
    if (chart_name == "1d") {
        cryptoChart = spawn_btc_chart(
            [<?php echo $btc_rates_data_arr['1d']['time']; ?>],
            [<?php echo $btc_rates_data_arr['1d']['values']; ?>], 
            <?php echo $btc_rates_data_arr['1d']['rate_min']; ?>,
            <?php echo $btc_rates_data_arr['1d']['rate_max']; ?>
        );
    }
    else
    if (chart_name == "1w") {
        cryptoChart = spawn_btc_chart(
            [<?php echo $btc_rates_data_arr['1w']['time']; ?>],
            [<?php echo $btc_rates_data_arr['1w']['values']; ?>], 
            <?php echo $btc_rates_data_arr['1w']['rate_min']; ?>,
            <?php echo $btc_rates_data_arr['1w']['rate_max']; ?>
        );
    }
    else
    if (chart_name == "1m") {
        cryptoChart = spawn_btc_chart(
            [<?php echo $btc_rates_data_arr['1m']['time']; ?>],
            [<?php echo $btc_rates_data_arr['1m']['values']; ?>], 
            <?php echo $btc_rates_data_arr['1m']['rate_min']; ?>,
            <?php echo $btc_rates_data_arr['1m']['rate_max']; ?>
        );
    }
    $("#btn-chart-" + chart_name).addClass("active");
}

// Создание графика
var cryptoChart = spawn_btc_chart(
            [<?php echo $btc_rates_data_arr['1h']['time']; ?>],
            [<?php echo $btc_rates_data_arr['1h']['values']; ?>], 
            <?php echo $btc_rates_data_arr['1h']['rate_min']; ?>,
            <?php echo $btc_rates_data_arr['1h']['rate_max']; ?>
);

// Обновить график при изменении размера окна
window.addEventListener('resize', () => {
  updateChartForMobile(cryptoChart);
});

$( document ).ready(function() {
	// Обновить график при загрузке страницы
    updateChartForMobile(cryptoChart);

});
</script>


<script src="/tmp_custom_code/js/main.js"></script>

<?php
require(DIR_WS_INCLUDES.'footer.php');
require(DIR_COMMON_PHP.'box_message.php');
?>
</body>
</html>