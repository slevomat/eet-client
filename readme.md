# Client for EET - Elektronická evidence tržeb

[![Build Status](https://img.shields.io/travis/slevomat/eet-client/master.svg?style=flat-square)](https://travis-ci.org/slevomat/eet-client)
[![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/slevomat/eet-client.svg?style=flat-square)](https://scrutinizer-ci.com/g/slevomat/eet-client/?branch=master)
[![Scrutinizer Code Quality](https://img.shields.io/scrutinizer/g/slevomat/eet-client.svg?style=flat-square)](https://scrutinizer-ci.com/g/slevomat/eet-client/?branch=master)
[![Latest Stable Version](https://img.shields.io/packagist/v/slevomat/eet-client.svg?style=flat-square)](https://packagist.org/packages/slevomat/eet-client)
[![Composer Downloads](https://img.shields.io/packagist/dt/slevomat/eet-client.svg?style=flat-square)](https://packagist.org/packages/slevomat/eet-client)

This repository provides a client library for electronic record of sales (EET) required by czech law.
Tento repozitář obsahuje klientskou knihovnu pro elektronickou evidenci tržeb (EET)

- [EET website](http://www.etrzby.cz)

## Instalace

Nejlepší způsob jak slevomat/eet-client nainstalovat je pomocí [Composeru](http://getcomposer.org/):

```
> composer require slevomat/eet-client
```

## Použití

**POZOR**: Všechny částky jsou uváděny v setinách měny. Tedy pokud chcete odeslat tržbu 55.5 Kč, vložíte do třídy `Receipt` hodnotu jako integer 5550.
```php
$crypto = new CryptographyService('cesta k privátnímu klíči', 'cesta k veřejnému klíči', 'heslo privátního klíče (nebo prázdný string pokud bez hesla)');
$configuration = new Configuration(
    'DIČ poplatníka',
    'Identifikace provozovny ',
    'Identifikace pokladního zařízení', 
    new EvidenceEnvironment(EvidenceEnvironment::PLAYGROUND), // nebo  new EvidenceEnvironment(EvidenceEnvironment::PRODUCTION) pro komunikaci s produkčním systémem
    false // zda zasílat účtenky v ověřovacím módu
);
$client = new Client($crypto, $configuration, new GuzzleSoapClientDriver(new \GuzzleHttp\Client()));

$receipt = new Receipt(
	true, 
	'CZ683555118',
	'0/6460/ZQ42',
	new \DateTimeImmutable('2016-11-01 00:30:12'),
    3411300
);

try {
    $response = $client->send($receipt);
    echo $response->getFik();
} catch (\SlevomatEET\FailedRequestException $e) {
    echo $e->getRequest()->getPkpCode(); // if request fails you need to print the PKP and BKP codes to receipt 
}
```

### Parametry účtenky

| XML jméno (dokumentace EET) | Popis                                                                           | Umístění v klientu                             | Poznámka               |
|-----------------------------|---------------------------------------------------------------------------------|------------------------------------------------|------------------------|
| uuid_zpravy                 | UUID zprávy                                                                     | `Receipt::$uuid`                               | automaticky generováno |
| dat_odesl                   | Datum odeslání tržby                                                            | `$response->getRequest()->getSendTime()`       | automaticky generováno |
| prvni_zaslani               | Příznak první zaslání                                                           | `Receipt::$firstSend`                          |                        |
| overeni                     | Příznak ověřovacího módu                                                        | `Configuration::$verificationMode`             | výchozí false          |
| dic_popl                    | DIČ poplatníka                                                                  | `Configuration::$vatId`                        |                        |
| dic_poverujiciho            | DIČ pověřujícího poplatníka                                                     | `Receipt::$delegatedVatId`                     |                        |
| id_provoz                   | ID provozovny                                                                   | `Configuration::$premiseId`                    |                        |
| id_pokl                     | ID pokladny                                                                     | `Configuration::$cashRegisterId`               |                        |
| porad_cis                   | Číslo účtenky                                                                   | `Receipt::$receiptNumber`                      |                        |
| dat_trzby                   | Datum uskutečnění tržby                                                         | `Receipt::$receiptTime`                        |                        |
| celk_trzba                  | Celková částka                                                                  | `Receipt::$totalPrice`                         |                        |
| zakl_nepodl_dph             | Celková částka plnění osvobozených od DPH, ostatních plnění                     | `Receipt::$priceZeroVat`                       |                        |
| zakl_dan1                   | Základ daně se základní sazbou DPH                                              | `Receipt::$priceStandardVat`                   |                        |
| dan1                        | DPH se základní sazbou                                                          | `Receipt::$vatStandard`                        |                        |
| zakl_dan2                   | Základ daně s první zníženou sazbou                                             | `Receipt::$priceFirstReducedVat`               |                        |
| dan2                        | DPH s první sníženou saznou                                                     | `Receipt::$vatFirstReduced`                    |                        |
| zakl_dan3                   | Základ daně s druhou sníženou sazbou                                            | `Receipt::$priceSecondReducedVat`              |                        |
| dan3                        | DPH s druhou sníženou sazbou                                                    | `Receipt::$vatSecondReduced`                   |                        |
| cest_sluz                   | Celková částka v režimu DPH pro cestovní službu                                 | `Receipt::$priceTravelService`                 |                        |
| pouzit_zboz1                | Celková částka v režimu DPH pro prodej použitého zboží se základní sazbou       | `Receipt::$priceUsedGoodsStandardVat`          |                        |
| pouzit_zboz2                | Celková částka v režimu DPH pro prodej použitého zboží s první sníženou sazbou  | `Receipt::$priceUsedGoodsFirstReduced`         |                        |
| pouzit_zboz3                | Celková částka v režimu DPH pro prodej použitého zboží s druhou sníženou sazbou | `Receipt::$priceUsedGoodsSecondReduced`        |                        |
| urceno_cerp_zuct            | Částka plateb určená k následnému čerpání nebo zúčtování                        | `Receipt::$priceSubsequentSettlement`          |                        |
| cerp_zuct                   | Částka plateb které jsou následným čerpáním nebo zúčtováním                     | `Receipt::$priceUsedSubsequentSettlement`      |                        |
| rezim                       | Režim tržby                                                                     | `Configuration::$evidenceMode`                 | výchozí bežný          |
| pkp                         | Podpisový kód poplatníka                                                        | `$response->getRequest()->getPkpCode()`        |                        |
| bkp                         | Bezpečnostní kód poplatníka                                                     | `$response->getRequest()->getBkpCode()`        |                        |

### Client driver

Odeslání požadavku na servery EET z neprobíhá přímo přes SoapClient integrovaný v PHP, ale pomocí rozhraní `SoapClientDriver`. Hlavním důvodem je 
nemožnost nastavení timeoutu požadavků integrovaného SoapClienta. 

Součástí knihovny je implentace rozhraní s pomocí [guzzlehttp/guzzle](https://packagist.org/packages/guzzlehttp/guzzle). Výchozí timeout této implementace 
je 2.5 sekundy, nastavitelný parametrem konstruktoru.
