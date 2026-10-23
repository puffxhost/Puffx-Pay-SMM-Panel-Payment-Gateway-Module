INSERT INTO `paymentmethods` (
    `methodName`, `methodLogo`, `methodVisibleName`, `methodCallback`,
    `methodMin`, `methodMax`, `methodFee`, `methodBonusPercentage`,
    `methodBonusStartAmount`, `methodCurrency`, `methodStatus`, `methodExtras`,
    `methodPosition`, `methodInstructions`
)
VALUES (
    'puffxpay',
    'puffx.png',
    'PuffX Pay',
    'puffx',
    10,
    100000,
    0,
    0,
    0,
    'INR',
    '1',
    '{ "route": 1 }',
    5,
    'Enter amount and proceed to payment. You will be redirected to PuffX gateway.'
);
