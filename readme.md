# Client for EET - Elektronická evidence tržeb

[![Build status](https://github.com/slevomat/eet-client/workflows/Build/badge.svg?branch=master)](https://github.com/slevomat/eet-client/actions?query=workflow%3ABuild+branch%3Amaster)
[![Code coverage](https://codecov.io/gh/slevomat/eet-client/branch/master/graph/badge.svg)](https://codecov.io/gh/slevomat/eet-client)
[![Latest Stable Version](https://img.shields.io/packagist/v/slevomat/eet-client.svg)](https://packagist.org/packages/slevomat/eet-client)
[![Composer Downloads](https://img.shields.io/packagist/dt/slevomat/eet-client.svg)](https://packagist.org/packages/slevomat/eet-client)

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
    EvidenceEnvironment::get(EvidenceEnvironment::PLAYGROUND), // nebo EvidenceEnvironment::get(EvidenceEnvironment::PRODUCTION) pro komunikaci s produkčním systémem
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
} catch (\SlevomatEET\InvalidResponseReceivedException $e) {
    echo $e->getResponse()->getRequest()->getPkpCode(); // on invalid response you need to print the PKP and BKP too
}
```

### Generování klíčů

Klíče obdržené z portálu Finanční správy jsou ve formátu .p12 a je třeba převést do formátu PEM.

V příkazové řádce proveďte tyto příkazy:

```$ openssl pkcs12 -in cesta/k/souboru.p12 -out public.pub -clcerts -nokeys```

```$ openssl pkcs12 -in cesta/k/souboru.p12 -out private.key -nocerts```

Cestu k výsledným `public.pub` a `private.key` pak nastavíte jako veřejný, resp. privátní klíč při vytváření `CryptographyService`

Pro testovací prostředí (playground) je třeba využít speciální testovací certifikáty. Tyto playground certifikáty jsou distribuovány s knihovnou v adresáři `cert`. Detailnější popis naleznete v dokumentaci [k testovacímu prostředí EET](http://www.etrzby.cz/cs/oznameni-k-testovacimu-prostredi-playground).

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
| pouzit_zboz2                | Celková částka v režimu DPH pro prodej použitého zboží s první sníženou sazbou  | `Receipt::$priceUsedGoodsFirstReducedVat`      |                        |
| pouzit_zboz3                | Celková částka v režimu DPH pro prodej použitého zboží s druhou sníženou sazbou | `Receipt::$priceUsedGoodsSecondReducedVat`     |                        |
| urceno_cerp_zuct            | Částka plateb určená k následnému čerpání nebo zúčtování                        | `Receipt::$priceSubsequentSettlement`          |                        |
| cerp_zuct                   | Částka plateb které jsou následným čerpáním nebo zúčtováním                     | `Receipt::$priceUsedSubsequentSettlement`      |                        |
| rezim                       | Režim tržby                                                                     | `Configuration::$evidenceMode`                 | výchozí bežný          |
| pkp                         | Podpisový kód poplatníka                                                        | `$response->getRequest()->getPkpCode()`        |                        |
| bkp                         | Bezpečnostní kód poplatníka                                                     | `$response->getRequest()->getBkpCode()`        |                        |

### Client driver

Odeslání požadavku na servery EET neprobíhá přímo přes SoapClient integrovaný v PHP, ale pomocí rozhraní `SoapClientDriver`. Hlavním důvodem je
nemožnost nastavení timeoutu požadavků integrovaného SoapClienta.

Součástí knihovny je implentace rozhraní s pomocí [guzzlehttp/guzzle](https://packagist.org/packages/guzzlehttp/guzzle). Výchozí timeout této implementace
je 2.5 sekundy, nastavitelný parametrem konstruktoru.
