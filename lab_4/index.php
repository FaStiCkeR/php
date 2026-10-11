<?php
declare(strict_types=1);

/**
 * Массив банковских транзакций.
 * Каждая транзакция — ассоциативный массив с полями:
 * id, date (Y-m-d), amount, description, merchant.
 *
 * @var array<int, array{id: int, date: string, amount: float, description: string, merchant: string}>
 */
$transactions = [
        [
                "id" => 1,
                "date" => "2019-01-01",
                "amount" => 100.00,
                "description" => "Payment for groceries",
                "merchant" => "SuperMart",
        ],
        [
                "id" => 2,
                "date" => "2020-02-15",
                "amount" => 75.50,
                "description" => "Dinner with friends",
                "merchant" => "Local Restaurant",
        ],
        [
                "id" => 3,
                "date" => "2021-06-10",
                "amount" => 42.99,
                "description" => "Monthly subscription",
                "merchant" => "StreamingService",
        ],
        [
                "id" => 4,
                "date" => "2022-11-23",
                "amount" => 250.00,
                "description" => "New headphones",
                "merchant" => "TechStore",
        ],
        [
                "id" => 5,
                "date" => "2023-03-08",
                "amount" => 18.75,
                "description" => "Flowers for mom",
                "merchant" => "FlowerShop",
        ],
];

addTransaction(6, "2024-05-01", 99.99, "Online course", "EduPlatform");

// Копии массива под разные сортировки: usort меняет массив по ссылке,
// поэтому сохраняем снимок после каждого шага.
usort($transactions, fn($a, $b) => $a["date"] <=> $b["date"]);
$byDate = $transactions;

usort($transactions, fn($a, $b) => $b["amount"] <=> $a["amount"]);
$byAmount = $transactions;

/**
 * Подсчитывает общую сумму всех транзакций.
 *
 * @param array<int, array{amount: float}> $transactions Список транзакций.
 * @return float Суммарная стоимость.
 */
function calculateTotalAmount(array $transactions): float
{
    $total = 0.0;
    foreach ($transactions as $transaction) {
        $total += $transaction["amount"];
    }
    return $total;
}

/**
 * Ищет транзакции по части описания (регистронезависимо).
 *
 * @param string $descriptionPart Подстрока для поиска в поле description.
 * @return array<int, array> Список найденных транзакций; пустой массив, если совпадений нет.
 */
function findTransactionByDescription(string $descriptionPart): array
{
    global $transactions;

    $result = [];
    foreach ($transactions as $transaction) {
        if (stripos($transaction["description"], $descriptionPart) !== false) {
            $result[] = $transaction;
        }
    }
    return $result;
}

/**
 * Ищет транзакцию по идентификатору (реализация через foreach).
 *
 * @param int $id Идентификатор транзакции.
 * @return array|null Транзакция или null, если не найдена.
 */
function findTransactionById(int $id): ?array
{
    global $transactions;

    foreach ($transactions as $transaction) {
        if ($transaction["id"] === $id) {
            return $transaction;
        }
    }
    return null;
}

/**
 * Ищет транзакцию по идентификатору через array_filter.
 *
 * array_filter сохраняет исходные ключи, поэтому берём первое значение
 * через array_values, чтобы корректно вернуть элемент независимо от позиции.
 *
 * @param int $id Идентификатор транзакции.
 * @return array|null Транзакция или null, если не найдена.
 */
function findTransactionByIdFilter(int $id): ?array
{
    global $transactions;

    $found = array_filter($transactions, fn($t) => $t["id"] === $id);

    return $found ? array_values($found)[0] : null;
}

/**
 * Считает количество полных дней с даты транзакции до сегодняшнего дня.
 *
 * @param string $date Дата в формате Y-m-d.
 * @return int Количество дней; 0, если дата некорректна.
 */
function daysSinceTransaction(string $date): int
{
    $transactionDate = strtotime($date);
    if ($transactionDate === false) {
        return 0;
    }

    // Приводим обе даты к полуночи, чтобы разница была целым числом суток.
    $today = strtotime('today');
    $diff = $today - $transactionDate;

    return (int)floor($diff / 86400);
}

/**
 * Добавляет новую транзакцию в глобальный массив $transactions.
 *
 * @param int $id Идентификатор транзакции.
 * @param string $date Дата в формате Y-m-d.
 * @param float $amount Сумма транзакции.
 * @param string $description Описание платежа.
 * @param string $merchant Название организации.
 * @return void
 */
function addTransaction(
        int    $id,
        string $date,
        float  $amount,
        string $description,
        string $merchant
): void
{
    global $transactions;

    $transactions[] = [
            "id" => $id,
            "date" => $date,
            "amount" => $amount,
            "description" => $description,
            "merchant" => $merchant,
    ];
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>PHP_lab4</title>
</head>
<body>
<h1>Сортировка по дате</h1>
<table border="1">
    <thead>
    <tr>
        <th>ID</th>
        <th>Дата</th>
        <th>Сумма</th>
        <th>Описание</th>
        <th>Магазин</th>
        <th>Дней прошло</th>
    </tr>
    </thead>

    <tbody>
    <?php foreach ($byDate as $transaction) {
        echo "<tr>";
        echo "<td>" . $transaction["id"] . "</td>";
        echo "<td>" . $transaction["date"] . "</td>";
        echo "<td>" . $transaction["amount"] . "</td>";
        echo "<td>" . $transaction["description"] . "</td>";
        echo "<td>" . $transaction["merchant"] . "</td>";
        echo "<td>" . daysSinceTransaction($transaction["date"]) . "</td>";
        echo "</tr>";
    } ?>
    </tbody>

    <tfoot>
    <tr>
        <td colspan="2"><strong>Итого:</strong></td>
        <?php echo "<td colspan='4'>" . calculateTotalAmount($transactions) . "</td>"; ?>
    </tr>
    </tfoot>
</table>

<h1>Сортировка по сумме</h1>
<table border="1">
    <thead>
    <tr>
        <th>ID</th>
        <th>Дата</th>
        <th>Сумма</th>
        <th>Описание</th>
        <th>Магазин</th>
        <th>Дней прошло</th>
    </tr>
    </thead>

    <tbody>
    <?php foreach ($byAmount as $transaction) {
        echo "<tr>";
        echo "<td>" . $transaction["id"] . "</td>";
        echo "<td>" . $transaction["date"] . "</td>";
        echo "<td>" . $transaction["amount"] . "</td>";
        echo "<td>" . $transaction["description"] . "</td>";
        echo "<td>" . $transaction["merchant"] . "</td>";
        echo "<td>" . daysSinceTransaction($transaction["date"]) . "</td>";
        echo "</tr>";
    } ?>
    </tbody>

    <tfoot>
    <tr>
        <td colspan="2"><strong>Итого:</strong></td>
        <?php echo "<td colspan='4'>" . calculateTotalAmount($transactions) . "</td>"; ?>
    </tr>
    </tfoot>
</table>

<hr>

<h3>Поиск по описанию: "friends"</h3>
<?php
$foundByDesc = findTransactionByDescription("friends");
foreach ($foundByDesc as $t): ?>
    <p>
        #<?= $t["id"] ?> — <?= htmlspecialchars($t["description"]) ?>
        (<?= htmlspecialchars($t["merchant"]) ?>, <?= $t["amount"] ?>)
    </p>
<?php endforeach; ?>

<h3>Поиск по ID = 3 (foreach)</h3>
<?php
$found = findTransactionById(3);
if ($found): ?>
    <p><?= htmlspecialchars($found["description"]) ?> — <?= $found["amount"] ?></p>
<?php else: ?>
    <p>Не найдено</p>
<?php endif; ?>

<h3>Поиск по ID = 3 (array_filter)</h3>
<?php
$foundFilter = findTransactionByIdFilter(3);
if ($foundFilter): ?>
    <p><?= htmlspecialchars($foundFilter["description"]) ?> — <?= $foundFilter["amount"] ?></p>
<?php else: ?>
    <p>Не найдено</p>
<?php endif; ?>

<?php
// Вывод всех изображений из папки image/
$dir = 'image/';
$files = scandir($dir);

if ($files === false) {
    return;
}

for ($i = 0; $i < count($files); $i++) {
    if (($files[$i] !== ".") && ($files[$i] !== "..")) {
        $path = $dir . $files[$i];
        echo '<img src="' . htmlspecialchars($path) . '" alt="" width="200" height="200">';
    }
}
?>
</body>
</html>

