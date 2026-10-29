<?php

declare(strict_types=1);

/**
 * Интерфейс хранилища транзакций.
 */
interface TransactionStorageInterface
{
    /**
     * Добавляет новую транзакцию.
     *
     * @param Transaction $transaction Добавляемая транзакция
     * @return void
     */
    public function addTransaction(Transaction $transaction): void;

    /**
     * Удаляет транзакцию по идентификатору.
     *
     * @param int $id Идентификатор транзакции
     * @return void
     */
    public function removeTransactionById(int $id): void;

    /**
     * Возвращает все транзакции.
     *
     * @return Transaction[]
     */
    public function getAllTransactions(): array;

    /**
     * Ищет транзакцию по идентификатору.
     *
     * @param int $id Идентификатор транзакции
     * @return Transaction|null Найденная транзакция или null
     */
    public function findById(int $id): ?Transaction;
}

class Transaction
{
    /** @var DateTime Дата транзакции */
    private DateTime $date;

    /**
     * Конструктор транзакции.
     *
     * @param int $id Уникальный идентификатор транзакции
     * @param string $date Дата транзакции в формате Y-m-d
     * @param float $amount Сумма транзакции
     * @param string $description Описание платежа
     * @param string $merchant Получатель платежа
     * @throws Exception
     */
    public function __construct(
        private int    $id,
        string         $date,
        private float  $amount,
        private string $description,
        private string $merchant
    )
    {
        $this->date = new DateTime($date);
    }

    /**
     * Возвращает идентификатор транзакции.
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Возвращает дату транзакции.
     * @return DateTime
     */
    public function getDate(): DateTime
    {
        return $this->date;
    }

    /**
     * Возвращает сумму транзакции.
     * @return float
     */
    public function getAmount(): float
    {
        return $this->amount;
    }

    /**
     * Возвращает описание платежа.
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Возвращает получателя платежа.
     * @return string
     */
    public function getMerchant(): string
    {
        return $this->merchant;
    }

    /**
     * Возвращает количество дней с момента транзакции до текущей даты.
     *
     * @return int Количество полных дней
     */
    public function getDaysSinceTransaction(): int
    {
        $today = new DateTime('today');
        $transactionDay = ($this->date)->setTime(0, 0, 0);

        return (int)$transactionDay->diff($today)->days;
    }
}

/**
 * Репозиторий отвечает только за хранение транзакций и базовый доступ к ним.
 */
class TransactionRepository implements TransactionStorageInterface
{
    /** @var Transaction[] Массив транзакций (ключ — id транзакции) */
    private array $transactions = [];

    /**
     * Добавляет новую транзакцию в коллекцию.
     *
     * @param Transaction $transaction Добавляемая транзакция
     * @return void
     */
    public function addTransaction(Transaction $transaction): void
    {
        $this->transactions[$transaction->getId()] = $transaction;
    }

    /**
     * Удаляет транзакцию по идентификатору.
     *
     * @param int $id Идентификатор транзакции
     * @return void
     */
    public function removeTransactionById(int $id): void
    {
        unset($this->transactions[$id]);
    }

    /**
     * Возвращает полный список транзакций.
     *
     * @return Transaction[]
     */
    public function getAllTransactions(): array
    {
        return array_values($this->transactions);
    }

    /**
     * Находит транзакцию по идентификатору.
     *
     * @param int $id Идентификатор транзакции
     * @return Transaction|null Транзакция или null, если не найдена
     */
    public function findById(int $id): ?Transaction
    {
        return $this->transactions[$id] ?? null;
    }
}

/**
 * Менеджер реализует бизнес-логику над коллекцией транзакций.
 */
class TransactionManager
{
    /**
     * @param TransactionStorageInterface $repository Хранилище транзакций
     */
    public function __construct(
        private TransactionStorageInterface $repository
    )
    {
    }

    /**
     * Вычисляет общую сумму всех транзакций.
     *
     * @return float
     */
    public function calculateTotalAmount(): float
    {
        return array_sum(array_map(
            fn(Transaction $t): float => $t->getAmount(),
            $this->repository->getAllTransactions()
        ));
    }

    /**
     * Вычисляет сумму транзакций за период (границы включительно).
     *
     * @param string $startDate Начало периода (Y-m-d)
     * @param string $endDate Конец периода (Y-m-d)
     * @return float
     */
    public function calculateTotalAmountByDateRange(string $startDate, string $endDate): float
    {
        $start = (new DateTime($startDate))->setTime(0, 0, 0);
        $end = (new DateTime($endDate))->setTime(23, 59, 59);

        $filtered = array_filter(
            $this->repository->getAllTransactions(),
            fn(Transaction $t): bool => $t->getDate() >= $start && $t->getDate() <= $end
        );

        return array_sum(array_map(
            fn(Transaction $t): float => $t->getAmount(),
            $filtered
        ));
    }

    /**
     * Подсчитывает количество транзакций по получателю.
     *
     * @param string $merchant Название получателя
     * @return int
     */
    public function countTransactionsByMerchant(string $merchant): int
    {
        return count(array_filter(
            $this->repository->getAllTransactions(),
            fn(Transaction $t): bool => $t->getMerchant() === $merchant
        ));
    }

    /**
     * Сортирует транзакции по дате (от старых к новым).
     *
     * @return Transaction[]
     */
    public function sortTransactionsByDate(): array
    {
        $transactions = $this->repository->getAllTransactions();
        usort(
            $transactions, fn(Transaction $a, Transaction $b): int => $a->getDate() <=> $b->getDate()
        );

        return $transactions;
    }

    /**
     * Сортирует транзакции по сумме по убыванию.
     *
     * @return Transaction[]
     */
    public function sortTransactionsByAmountDesc(): array
    {
        $transactions = $this->repository->getAllTransactions();
        usort(
            $transactions,
            fn(Transaction $a, Transaction $b): int => $b->getAmount() <=> $a->getAmount()
        );

        return $transactions;
    }
}

/**
 * Формирует HTML-таблицу из списка транзакций.
 */
final class TransactionTableRenderer
{
    /**
     * Соответствие «получатель → категория».
     * Категория не хранится в Transaction, поэтому определяется при выводе.
     *
     * @var array<string, string>
     */
    private const CATEGORIES = [
        'Магнит' => 'Продукты',
        'Лукойл' => 'Транспорт',
        'Netflix' => 'Развлечения',
        'Аптека 36.6' => 'Здоровье',
        'Ozon' => 'Покупки',
        'МТС' => 'Связь',
        'Яндекс Такси' => 'Транспорт',
        'Cinema Park' => 'Развлечения',
        'Университет' => 'Образование',
        'ЖКХ' => 'Коммунальные услуги',
    ];

    /**
     * Возвращает HTML-таблицу транзакций.
     *
     * @param Transaction[] $transactions Массив транзакций
     * @return string HTML-код таблицы
     */
    public function render(array $transactions): string
    {
        $html = '<table border="1" cellpadding="6" cellspacing="0">';
        $html .= '<thead><tr>'
            . '<th>ID</th><th>Дата</th><th>Сумма</th><th>Описание</th>'
            . '<th>Получатель</th><th>Категория</th><th>Дней назад</th>'
            . '</tr></thead><tbody>';

        foreach ($transactions as $t) {
            $html .= '<tr>'
                . '<td>' . $t->getId() . '</td>'
                . '<td>' . $t->getDate()->format('Y-m-d') . '</td>'
                . '<td>' . number_format($t->getAmount(), 2, '.', ' ') . '</td>'
                . '<td>' . $this->escape($t->getDescription()) . '</td>'
                . '<td>' . $this->escape($t->getMerchant()) . '</td>'
                . '<td>' . $this->escape($this->getCategory($t->getMerchant())) . '</td>'
                . '<td>' . $t->getDaysSinceTransaction() . '</td>'
                . '</tr>';
        }

        return $html . '</tbody></table>';
    }

    /**
     * Экранирует строку для безопасного вывода в HTML.
     *
     * @param string $value Исходная строка
     * @return string
     */
    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Определяет категорию получателя.
     *
     * @param string $merchant Название получателя
     * @return string Категория («Прочее», если получатель неизвестен)
     */
    private function getCategory(string $merchant): string
    {
        return self::CATEGORIES[$merchant] ?? 'Прочее';
    }
}

// Начальные данные

$repository = new TransactionRepository();

$transactions = [
    new Transaction(1, '2025-01-05', 1250.50, 'Покупка продуктов', 'Магнит'),
    new Transaction(2, '2025-01-12', 3200.00, 'Заправка автомобиля', 'Лукойл'),
    new Transaction(3, '2025-02-01', 799.00, 'Подписка на сервис', 'Netflix'),
    new Transaction(4, '2025-02-14', 540.75, 'Лекарства', 'Аптека 36.6'),
    new Transaction(5, '2025-03-03', 8990.00, 'Заказ электроники', 'Ozon'),
    new Transaction(6, '2025-03-20', 450.00, 'Пополнение мобильной связи', 'МТС'),
    new Transaction(7, '2025-04-08', 380.00, 'Поездка на такси', 'Яндекс Такси'),
    new Transaction(8, '2025-04-25', 1100.00, 'Билеты в кино', 'Cinema Park'),
    new Transaction(9, '2025-05-10', 45000.00, 'Оплата обучения', 'Университет'),
    new Transaction(10, '2025-05-28', 4300.40, 'Оплата коммунальных услуг', 'ЖКХ'),
    new Transaction(11, '2025-06-02', 980.30, 'Покупка продуктов', 'Магнит'),
];

foreach ($transactions as $transaction) {
    $repository->addTransaction($transaction);
}

$manager = new TransactionManager($repository);
$renderer = new TransactionTableRenderer();

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Банковские транзакции</title>
</head>
<body>
<h1>Все транзакции</h1>
<?php echo $renderer->render($repository->getAllTransactions()); ?>

<h2>Сортировка по дате</h2>
<?php echo $renderer->render($manager->sortTransactionsByDate()); ?>

<h2>Сортировка по сумме (по убыванию)</h2>
<?php echo $renderer->render($manager->sortTransactionsByAmountDesc()); ?>

<h2>Вычисления</h2>
<ul>
    <li>Общая сумма: <?php echo number_format($manager->calculateTotalAmount(), 2, '.', ' '); ?></li>
    <li>Сумма за период 2025-01-01 — 2025-03-31:
        <?php echo number_format($manager->calculateTotalAmountByDateRange('2025-01-01', '2025-03-31'), 2, '.', ' '); ?></li>
    <li>Транзакций по «Магнит»: <?php echo $manager->countTransactionsByMerchant('Магнит'); ?></li>
</ul>

<?php
// Демонстрация поиска и удаления
$found = $repository->findById(5);
$repository->removeTransactionById(11);
?>
<h2>Поиск и удаление</h2>
<p>Найдена транзакция #5: <?php echo $found !== null ? htmlspecialchars($found->getDescription()) : 'не найдена'; ?></p>
<p>Транзакция #11 удалена. Осталось: <?php echo count($repository->getAllTransactions()); ?></p>
<?php echo $renderer->render($repository->getAllTransactions()); ?>
</body>
</html>