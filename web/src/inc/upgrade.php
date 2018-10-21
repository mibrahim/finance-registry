<?php

// Upgrade the database to the current version
$highestversion = $dbver;

if ($highestversion < "0001") {
    // Upgrade to version 0001
    query("BEGIN TRANSACTION");

    query("CREATE TABLE variables(name TEXT PRIMARY KEY,value TEXT)");

    query("CREATE TABLE date_type(id SERIAL PRIMARY KEY, name TEXT, description TEXT)");
    query("INSERT INTO date_type(name, description) VALUES ('bank','Date posted by the bank')");
    query("INSERT INTO date_type(name, description) VALUES ('plan','Date used for planning')");

    query("CREATE TABLE account_groups(id SERIAL PRIMARY KEY, name TEXT, description TEXT, parent_id BIGINT REFERENCES account_groups)");
    query("INSERT INTO account_groups(name, description) VALUES ('Income','Income accounts')"); // 1
    query("INSERT INTO account_groups(name, description, parent_id) VALUES ('Salaries','Collected salaries',1)"); // 2
    query("INSERT INTO account_groups(name, description, parent_id) VALUES ('Commissions','Collected commissions',1)"); // 3
    query("INSERT INTO account_groups(name, description, parent_id) VALUES ('Dividends/Payouts','Corporate dividends and payouts',1)"); // 4

    query("INSERT INTO account_groups(name, description) VALUES ('Expenses','Expenses accounts')"); // 5
    query("INSERT INTO account_groups(name, description, parent_id) VALUES ('Car/Auto','Expenses related to cars', 5)"); // 6
    query("INSERT INTO account_groups(name, description, parent_id) VALUES ('Utilities','Utilities', 5)"); // 7
    query("INSERT INTO account_groups(name, description, parent_id) VALUES ('Mortgages/Rents','Mortgages and rents',5)"); // 8
    query("INSERT INTO account_groups(name, description, parent_id) VALUES ('Education','Education',5)"); // 9
    query("INSERT INTO account_groups(name, description, parent_id) VALUES ('Loans/Credit cards','Loans and credit cards',5)"); // 10

    query("INSERT INTO account_groups(name, description) VALUES ('Bank accounts','Bank accounts')"); // 11
    query("INSERT INTO account_groups(name, description, parent_id) VALUES ('Checking','Checking accounts', 11)"); // 10
    query("INSERT INTO account_groups(name, description, parent_id) VALUES ('Savings','Savings accounts', 11)"); // 10

    query("INSERT INTO account_groups(name, description) VALUES ('Credit cards / Loans','Credit cards and loans')");

    query("CREATE TABLE entity(id SERIAL PRIMARY KEY, name TEXT, description TEXT)");

    query("CREATE TABLE account(id SERIAL PRIMARY KEY, name TEXT, description TEXT,
                                  group_id BIGINT REFERENCES account_groups,
                                  entity_id BIGINT REFERENCES entity,
                                  is_favorite int
                                  )");

    query("CREATE TABLE transaction( id SERIAL PRIMARY KEY,
                                            name TEXT, description TEXT,
                                            json_data TEXT,
                                            account_id BIGINT REFERENCES account)");

    query("CREATE INDEX idx_transaction_account on transaction(account_id)");

    query("CREATE TABLE transaction_splits(
                                  id SERIAL PRIMARY KEY,
                                  name TEXT,
                                  description TEXT,
                                  transaction_id BIGINT REFERENCES transaction,
                                  amount DOUBLE PRECISION,
                                  account_id BIGINT REFERENCES account
                                )");

    query("CREATE INDEX idx_transaction_splits_transaction on transaction_splits(transaction_id)");
    query("CREATE INDEX idx_transaction_splits_account on transaction_splits(account_id)");

    query("CREATE TABLE transaction_splits_dates(
                                  split_id BIGINT REFERENCES transaction_splits,
                                  date_type_id BIGINT REFERENCES date_type,
                                  timestamp BIGINT,
                                  running_balance DOUBLE PRECISION,
                                  amount DOUBLE PRECISION,
                                  account_id BIGINT REFERENCES account
                                )");
    query("CREATE INDEX idx_transaction_splits_accounts_dates on transaction_splits_dates(account_id, date_type_id)");
    query("CREATE INDEX idx_transaction_splits_timestamp on transaction_splits_dates(timestamp)");

    query("COMMIT");

    $highestversion = "0001";
}

if ($highestversion < $sysversion) {
    die("Error while upgrading system");
}

setvar("sysversion", $highestversion);