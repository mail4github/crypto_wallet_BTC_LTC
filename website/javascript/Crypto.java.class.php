<?php
require('../../includes/application_top.php');
?>
class Crypto {
	//crypto_name: "bitcoin",
	//crypto_symbol: "BTC",
	//symbol: "&#3647;",
	//digits: 5,
	// php works: <?php echo DIR_WS_INCLUDES; ?>
	COIN: 100000000,
	
	constructor ( name_of_crypto )
	{
		this.get_crypto_currency_by_name(name_of_crypto);
	}
	
	get_crypto_currency_by_name( name_of_crypto )
	{
		switch ( name_of_crypto.toLowerCase() ) {
			case "btc" :
			case "bitcoin" :
				this.crypto_name = "bitcoin";
				this.crypto_symbol = "BTC";
				this.symbol = "&#3647;";
				this.digits = 5;
			break;
			case "ltc" :
			case "litecoin" :
				this.crypto_name = "litecoin";
				this.crypto_symbol = "LTC";
				this.symbol = "&#321;";
				this.digits = 3;
			break;
			default:
				var crypto_name = this.get_crypto_currency_by_address(name_of_crypto);
				if ( crypto_name )
					this.get_crypto_currency_by_name(crypto_name);
		}
	}

	get_crypto_currency_by_address( address )
	{
		if ( address.charAt(0) == "1" || address.charAt(0) == "3" )
			return "bitcoin";
		if ( address.charAt(0) == "L" || address.charAt(0) == "M" )
			return "litecoin";
		return false;
	}
	
	to_satoshis( btcAmount )
    {
        return btcAmount * this.COIN;
    }

	from_satoshis( amount_in_satoshis )
    {
        return amount_in_satoshis / this.COIN;
    }

	checkout_confirmations (tx_hash, callback, source)
	{
		if ( typeof source == "undefined" )
			source = Math.floor(Math.random() * 4) + 0;
		try {
			switch ( source ) {
				case 0:
				$.get("https://api.blockchair.com/" + this.crypto_name + "/dashboards/transaction/" + tx_hash, "", function() {
				}).done(function(response) {
				}).fail(function(response) {
				}).always(function(response) {
					if ( typeof response["data"] != "undefined" && typeof response["data"][tx_hash]["transaction"]["block_id"] != "undefined" ) {
						if ( response["data"][tx_hash]["transaction"]["block_id"] > 0 )
							callback(tx_hash, "confirmed");
						else 
							callback(tx_hash, "not_confirmed");
					}
				});
				break;
				case 1:
				$.get("https://api.smartbit.com.au/v1/blockchain/tx/" + tx_hash, "", function() {
				}).done(function(response) {
				}).fail(function(response) {
				}).always(function(response) {
					if ( typeof response["success"] != "undefined" && response["success"] ) {
						if ( response["transaction"]["confirmations"] > 0 )
							callback(tx_hash, "confirmed");
						else 
							callback(tx_hash, "not_confirmed");
					}
				});
				break;
				case 2:
				$.get("https://chain.api.btc.com/v3/tx/" + tx_hash, "", function() {
				}).done(function(response) {
				}).fail(function(response) {
				}).always(function(response) {
					if ( typeof response["data"] != "undefined" && typeof response["data"]["confirmations"] != "undefined" ) {
						if ( response["data"]["confirmations"] > 0 )
							callback(tx_hash, "confirmed");
						else 
							callback(tx_hash, "not_confirmed");
					}
				});
				break;
				case 3:
				$.post("/api/cryptwallet_api_call", {token: "<?php echo get_api_token_seed(); ?>", crypto_name: this.crypto_name, method: "getrawtransaction", params: [tx_hash, true]}, function() {
				}).done(function(response) {
				}).fail(function(response) {
				}).always(function(response) {
					response = JSON.parse(response);
					if ( typeof response["success"] != "undefined" && response["success"] ) {
						if ( response["values"]["confirmations"] > 0 )
							callback(tx_hash, "confirmed");
						else 
							callback(tx_hash, "not_confirmed");
					}
				});
			}
		} catch(error) {
			console.error(error);
		}
		return false;
	}

	get_list_of_transactions(addr, callback, callback_on_error, source)
	{
		var res = [];
		if ( typeof source == "undefined" )
			source = Math.floor(Math.random() * 3) + 0;
		try {
			switch ( source ) {
				case 0:
				$.get("https://api.smartbit.com.au/v1/blockchain/address/" + addr, "", function() {
				}).done(function(response) {
				}).fail(function(response) {
				}).always(function(response) {
					try {
						if ( typeof response["success"] != "undefined" && response["success"] ) {
							if ( typeof response["address"] != "undefined" && typeof response["address"]["transactions"] != "undefined" && response["address"]["transactions"].length > 0 ) {
								for (var i = 0; i < response["address"]["transactions"].length; i++) {
									var transaction = response["address"]["transactions"][i];
									var found_tx = {time: transaction["first_seen"], hash: transaction["txid"], block_number: ( typeof transaction["block"] != "undefined" ? transaction["block"] : 0), inputs: [], outputs: []};
									for (var j = 0; j < transaction["inputs"].length; j++) {
										var input = transaction["inputs"][j];
										var found_input = {address: "", value: 0.0};
										if ( typeof input["addresses"][0] != "undefined" )
											found_input["address"] = input["addresses"][0];
										if ( typeof input["value"] != "undefined" )
											found_input["value"] = input["value"];
										found_tx["inputs"].push(found_input);
									}
									for (var j = 0; j < transaction["outputs"].length; j++) {
										var output = transaction["outputs"][j];
										var found_output = {address: "", value: 0.0};
										if ( typeof output["addresses"][0] != "undefined" )
											found_output["address"] = output["addresses"][0];
										if ( typeof output["value"] != "undefined" )
											found_output["value"] = output["value"];
										found_tx["outputs"].push(found_output);
									}
									res.push(found_tx);
								}
								callback( res, addr );
							}
						}
						callback( false );
					} catch(error) {
						console.error(error);
					}
					if ( typeof callback_on_error != "undefined" )
						callback_on_error();
				});
				break;
				case 1:
				$.get("https://api.blockchair.com/" + this.crypto_name + "/dashboards/address/" + addr + "?transaction_details=true", "", function() {
				}).done(function(response) {
				}).fail(function(response) {
				}).always(function(response) {
					if ( typeof response["data"] != "undefined" && typeof response["data"][addr]["transactions"] != "undefined" ) {
						for (var i = 0; i < response["data"][addr]["transactions"].length; i++) {
							var transaction = response["data"][addr]["transactions"][i];
							if (transaction["balance_change"] <= 0)
								continue;
							var found_tx = {time: transaction["time"], hash: transaction["hash"], block_number: ( typeof transaction["block_id"] != "undefined" ? transaction["block_id"] : 0), inputs: [], outputs: []};
							var found_output = {address: addr, value: transaction["balance_change"] / 100000000};
							found_tx["outputs"].push(found_output);
							res.push(found_tx);
						}
						callback( res, addr );
					}
					callback( false );
				});
				break;
				case 2:
				$.get("https://api.blockcypher.com/v1/" + this.crypto_symbol.toLowerCase() + "/main/addrs/" + addr, "", function() {
				}).done(function(response) {
				}).fail(function(response) {
				}).always(function(response) {
					if ( typeof response["address"] != "undefined" && response["address"] && typeof response["txrefs"] != "undefined" ) {
						for (var i = 0; i < response["txrefs"].length; i++) {
							var transaction = response["txrefs"][i];
							if (transaction["tx_output_n"] < 0)
								continue;
							var found_tx = {time: transaction["confirmed"], hash: transaction["tx_hash"], block_number: ( typeof transaction["block_id"] != "undefined" ? transaction["block_id"] : 0), inputs: [], outputs: []};
							var found_output = {address: addr, value: transaction["value"]};
							found_tx["outputs"].push(found_output);
							res.push(found_tx);
						}
						callback( res, addr );
					}
				});
				break;
			}
		} catch(error) {
			console.error(error);
			if ( typeof callback_on_error != "undefined" )
				callback_on_error(error);
		}
		return false;
	}

	get_balance(address, callback, callback_on_error, source)
	{
		if ( typeof source == "undefined" )
			source = Math.floor(Math.random() * 5) + 0;
		try {
			switch ( source ) {
				case 0:
				$.get("https://blockchain.info/q/addressbalance/" + address + "?confirmations=0", "", function() {
				}).done(function(response) {
				}).fail(function(response) {
				}).always(function(response) {
					try {
						if ( typeof response != "undefined" && response.length > 0 && parseInt(response) >= 0 ) {
							callback( parseInt(response) / 100000000, address );
						}
						else
						if ( typeof callback_on_error != "undefined" )
							callback_on_error();
					} catch(error) {
						console.error(error);
						if ( typeof callback_on_error != "undefined" )
							callback_on_error(error);
					}
				});
				break;
				case 1:
				$.get("https://api.blockcypher.com/v1/" + this.crypto_symbol.toLowerCase() + "/main/addrs/" + address + "/balance", "", function() {
				}).done(function(response) {
				}).fail(function(response) {
				}).always(function(response) {
					try {
						if ( typeof response["address"] != "undefined" && response["address"] == address && typeof response["final_balance"] != "undefined" ) {
							callback( parseInt(response["final_balance"]) / 100000000, address );
						}
						else
						if ( typeof callback_on_error != "undefined" )
							callback_on_error();
					} catch(error) {
						console.error(error);
						if ( typeof callback_on_error != "undefined" )
							callback_on_error(error);
					}
				});
				break;
				case 2:
				$.get("https://api.blockchair.com/" + this.crypto_name.toLowerCase() + "/dashboards/address/" + address + "", "", function() {
				}).done(function(response) {
				}).fail(function(response) {
				}).always(function(response) {
					try {
						if ( typeof response["data"][address]["address"]["balance"] != "undefined" ) {
							callback( parseInt(response["data"][address]["address"]["balance"]) / 100000000, address );
						}
						else
						if ( typeof callback_on_error != "undefined" )
							callback_on_error();
					} catch(error) {
						console.error(error);
						if ( typeof callback_on_error != "undefined" )
							callback_on_error(error);
					}
				});
				break;
				case 3:
				$.get("https://api.smartbit.com.au/v1/blockchain/address/" + address + "/?limit=1", "", function() {
				}).done(function(response) {
				}).fail(function(response) {
				}).always(function(response) {
					try {
						if ( typeof response["success"] != "undefined" && response["success"] && typeof response["address"]["address"] != "undefined" && response["address"]["address"] == address && typeof response["address"]["total"]["balance"] != "undefined"  ) {
							callback( parseFloat(response["address"]["total"]["balance"]), address );
						}
						else
						if ( typeof callback_on_error != "undefined" )
							callback_on_error();
					} catch(error) {
						console.error(error);
						if ( typeof callback_on_error != "undefined" )
							callback_on_error(error);
					}
				});
				break;
				/*case 4:
				$.get("https://chain.api.btc.com/v3/address/" + address, "", function() {
				}).done(function(response) {
				}).fail(function(response) {
				}).always(function(response) {
					try {
						if ( typeof response["data"] != "undefined" && typeof response["data"]["address"] != "undefined" && response["data"]["address"] == address ) {
							callback( ( parseInt(response["data"]["balance"]) + parseInt(response["data"]["unconfirmed_received"]) - parseInt(response["data"]["unconfirmed_sent"]) ) / 100000000, address );
						}
						else
						if ( typeof callback_on_error != "undefined" )
							callback_on_error();
					} catch(error) {
						console.error(error);
						if ( typeof callback_on_error != "undefined" )
							callback_on_error(error);
					}
				});*/
			}
		} catch(error) {
			console.error(error);
			if ( typeof callback_on_error != "undefined" )
				callback_on_error(error);
		}
		return false;
	}
}
