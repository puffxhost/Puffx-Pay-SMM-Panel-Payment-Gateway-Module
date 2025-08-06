INSERT INTO `paymentmethods` (
    `methodName`, `methodLogo`, `methodVisibleName`, `methodCallback`,
    `methodMin`, `methodMax`, `methodFee`, `methodBonusPercentage`,
    `methodBonusStartAmount`, `methodCurrency`, `methodStatus`, `methodExtras`,
    `methodPosition`, `methodInstructions`
)
VALUES (
    'puffxpay', -- internal slug
    'puffx.png', -- logo file
    'PuffX Pay', -- visible name
    'puffx', -- callback file (we'll create app/controller/payment/puffx.php)
    10, -- min deposit
    100000, -- max deposit
    0, -- fee percentage
    0, -- bonus %
    0, -- bonus starts
    'INR', -- currency
    '1', -- status (enabled)
    '{ "user_token": "9856ce42fc26349fe5fab9c6b630e9c6", "route": 1 }', -- methodExtras JSON
    5, -- position
    'Enter amount and proceed to payment. You will be redirected to PuffX gateway.' -- Instructions
);
