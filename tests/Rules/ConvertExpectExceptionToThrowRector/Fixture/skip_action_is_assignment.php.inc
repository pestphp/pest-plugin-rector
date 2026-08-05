<?php

// --- Skip: the statement after expectException() is an ASSIGNMENT ---
// Wrapping it would put the assignment inside the arrow function, so $orderItem never
// reaches the enclosing scope — and the call that actually throws is further down,
// which would leave it outside any expectation.
$this->expectException(RuntimeException::class);

$orderItem = createOrderItem(['sku' => null]);

(new CreateOrderLine)->execute($orderItem);

// --- Skip: compound assignment, same reason ---
$this->expectException(LogicException::class);

$total += chargeThatThrows();
