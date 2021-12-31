<?php

// Upgrade the database to the current version
$highestversion = $dbver;

if ($highestversion < "0001") {
	// Upgrade to version 0001
	query("BEGIN TRANSACTION");

	query("CREATE TABLE variables(name text, value text)");

	query("CREATE TABLE txns(
			key INTEGER PRIMARY KEY AUTOINCREMENT, 
			account text, 
			entity text, 
			status text, 
			description text,
			target text, 
			amount real, 
			running_balance real, 
			date INTEGER, 
			ord INTEGER,
			notes text,
			url text,
			json text)");

	query("CREATE INDEX idx_date on txns(date)");
	query("CREATE INDEX idx_ord on txns(date, ord, entity, account)");
	query("CREATE INDEX idx_ent on txns(entity)");
	query("CREATE INDEX idx_account on txns(account)");
	query("CREATE INDEX idx_ent_account on txns(entity, account)");

	query("COMMIT");

	$highestversion = "0001";
}

if ($highestversion < "0002") {
	// Upgrade to version 0002
	query("BEGIN TRANSACTION");

	query("CREATE TABLE todo(key INTEGER PRIMARY KEY AUTOINCREMENT, title text, description text, status text)");
	query("CREATE INDEX idx_todo_status on todo(status)");

	query("COMMIT");

	$highestversion = "0002";
}

if ($highestversion < "0003") {
	// Upgrade to version 0003
	query("BEGIN TRANSACTION");

	query("ALTER TABLE txns add lastmodified INTEGER");
	query("CREATE INDEX lastmodified on txns(lastmodified)");

	query("update txns set lastmodified=" . time());

	query("update txns set ord=10*ord");

	query("COMMIT");

	$highestversion = "0004";
}

if ($highestversion < $sysversion) {
	die("Error while upgrading system");
}

setVar("sysversion", $highestversion);
