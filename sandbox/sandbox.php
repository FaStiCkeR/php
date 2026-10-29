<?php

declare(strict_types=1);

// 1. Создаём массив $characters с 4 персонажами
$characters = [
    [
        "name" => "Jobby",
        "class" => "Dark Knight",
        "attack_type" => "Physical",
        "mana" => 0,
        "health" => 100,
        "damage" => 10
    ],
    [
        "name" => "Lizzel",
        "class" => "Mage",
        "attack_type" => "Magical",
        "mana" => 100,
        "health" => 50,
        "damage" => 5
    ],
    [
        "name" => "Aliot",
        "class" => "Mage",
        "attack_type" => "Magical",
        "mana" => 50,
        "health" => 70,
        "damage" => 7
    ],
    [
        "name" => "Ariel",
        "class" => "Demon Hunter",
        "attack_type" => "Physical",
        "mana" => 0,
        "health" => 80,
        "damage" => 8
    ]
];

// 2. Функция showCharacters
// Принимает массив и название столбца, выводит список значений
function showCharacters(array $characters, string $column): void
{
    echo "<ul>";
    foreach ($characters as $character) {
        if (isset($character[$column])) {
            echo "<li>" . $character[$column]   . "</li>";
        }
    }
    echo "</ul>";
}

// 3. Функция countCharacters
// Возвращает количество персонажей
function countCharacters(array $characters): int
{
    return count($characters);
}

// 4. Функция countCharactersByClass
// Возвращает ассоциативный массив: класс => количество
function countCharactersByClass(array $characters): array
{
    // Предварительно заполняем все возможные классы нулями
    $classes = ["Dark Knight", "Mage", "Necromancer", "Demon Hunter"];
    $result = array_fill_keys($classes, 0);

    foreach ($characters as $character) {
        $class = $character['class'];
        if (isset($result[$class])) {
            $result[$class]++;
        }
    }

    return $result;
}

// 5. Функция filterCharactersByAttackType
// Возвращает индексированный массив имён персонажей с указанным типом атаки
function filterCharactersByAttackType(array $characters, string $attackType): array
{
    $result = [];
    foreach ($characters as $character) {
        if ($character['attack_type'] === $attackType) {
            $result[] = $character['name'];
        }
    }
    return $result;
}

// 6. Функция calculateAverageDamage
// Возвращает средний урон всех персонажей
function calculateAverageDamage(array $characters): float
{
    if (empty($characters)) {
        return 0.0;
    }
    $totalDamage = 0;
    foreach ($characters as $character) {
        $totalDamage += $character['damage'];
    }
    return $totalDamage / count($characters);
}

// 7. Функция castSpell
// Принимает массив, id персонажа и список заклинаний
function castSpell(array $characters, int $id, string ...$spells): bool
{
    // Проверяем, существует ли персонаж с таким id
    if (!isset($characters[$id])) {
        return false;
    }

    $character = $characters[$id];

    // Проверяем, является ли персонаж магом
    if ($character['class'] !== 'Mage') {
        echo $character['name'] . " - не маг" . PHP_EOL;
        return false;
    }

    // Выводим заклинания
    foreach ($spells as $spell) {
        echo $character['name'] . " casts " . $spell . PHP_EOL;
    }

    return true;
}

// ==========================================
// ДЕМОНСТРАЦИЯ РАБОТЫ ВСЕХ ФУНКЦИЙ
// ==========================================

echo "<h3>2. Вывод столбца 'name':</h3>";
showCharacters($characters, "name");

echo "<h3>3. Количество персонажей:</h3>";
$charactersCount = countCharacters($characters);
echo $charactersCount . "<br>"; // 4

echo "<h3>4. Количество по классам:</h3>";
$classStats = countCharactersByClass($characters);
print_r($classStats);
// Ожидаемый результат: [Dark Knight => 1, Mage => 2, Necromancer => 0, Demon Hunter => 1]

echo "<h3>5. Фильтр по типу атаки 'Magical':</h3>";
$magicalCharacters = filterCharactersByAttackType($characters, "Magical");
print_r($magicalCharacters);
// Ожидаемый результат: [Lizzel, Aliot]

echo "<h3>6. Средний урон:</h3>";
$avgDamage = calculateAverageDamage($characters);
echo $avgDamage . "<br>"; // 7.5

echo "<h3>7. Использование заклинаний:</h3>";
// Lizzel (id=1) — маг, использует Fireball и Frost Nova
castSpell($characters, 1, "Fireball", "Frost Nova");

// Jobby (id=0) — не маг
castSpell($characters, 0, "Slash");