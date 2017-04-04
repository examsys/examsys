@core @backend
Feature: Cleaning variables
  In order to ensure that user input is safe
  As a developer
  I need to ensure that input is validated correctly

  Scenario Outline: Verifying alpha input
    Given I clean "<input>" as "ALPHA"
    Then the clean result should be "<output>"

    Examples:
      | input | output |
      | test string | test string |
      | test 46 string | test  string |
      | 42 | null |
      | 4.2 | null |
      | 0xF | xF |
      | www.example.com | wwwexamplecom |
      | Test <script><!-- alert(\'Test\') --></script> text | Test  text |
      | Test > text | Test  text |
      | Café | Café |
      | æìřčžšíýúáÚůú | æìřčžšíýúáÚůú |

  Scenario Outline: Verifying alpha numeric input
    Given I clean "<input>" as "ALPHANUM"
    Then the clean result should be "<output>"

    Examples:
      | input | output |
      | test string | test string |
      | test 46 string | test 46 string |
      | 42 | 42 |
      | 4.2 | 42 |
      | 0xF | 0xF |
      | www.example.com | wwwexamplecom |
      | Test <script><!-- alert(\'Test\') --></script> text | Test  text |
      | Test > text | Test  text |
      | Café | Café |
      | æìřčžšíýúáÚůú | æìřčžšíýúáÚůú |

  Scenario Outline: Verifying boolean input
    Given I clean "<input>" as "BOOLEAN"
    Then the clean result should be "<output>"

    Examples:
      | input | output |
      | true | true |
      | 1 | true |
      | on | true |
      | yes | true |
      | false | false |
      | 0 | false |
      | off | false |
      | no | false |
      | text | null |

  Scenario Outline: Verifying email input
    Given I clean "<input>" as "EMAIL"
    Then the clean result should be "<output>"

    Examples:
      | input | output |
      | joe@example.com | joe@example.com |
      | joe.bloggs@example.com | joe.bloggs@example.com |
      | joe-bloggs@example.com | joe-bloggs@example.com |
      | joe@sub.example.com | joe@sub.example.com |
      | 0xF | null |
      | www.example.com | null |
      | @example.com | null |
      | go@ten | null |

  Scenario Outline: Verifying floating point input
    Given I clean "<input>" as "FLOAT"
    Then the clean result should be "<output>"

    Examples:
      | input | output |
      | 1 | 1 |
      | 1.1 | 1.1 |
      | -1 | -1 |
      | -1.1 | -1.1 |
      | 0 | 0 |
      | 0xF | null |
      | 128.243.48.6 | null |

  Scenario Outline: Verifying html input
    Given I clean "<input>" as "HTML"
    Then the clean result should be "<output>"

    Examples:
      | input | output |
      | Test <script><!-- alert(\'Test\') --></script> text | Test  text |
      | Test <style><!-- p {color: red} --></style> text | Test  text |
      | <p>Test text</p> | <p>Test text</p> |
      | http://example.com/ | http://example.com/ |
      | 0 | 0 |
      | 0xF | 0xF |
      | 128.243.48.6 | 128.243.48.6 |
      | Test > text | Test &gt; text |
      | Café | Café |
      | æìřčžšíýúáÚůú | æìřčžšíýúáÚůú |
      | <p>Test<img src=j&#X41vascript:alert('test2')></p> | <p>Test</p> |
      | Test <br> test | Test <br /> test |
      | <p>Test | <p>Test</p> |
      | <div><p>Test</div></p> | <div><p>Test</p></div> |
      | Test<iframe src='http://example.com'></iframe> | Test |

  Scenario Outline: Verifying integer input
    Given I clean "<input>" as "INT"
    Then the clean result should be "<output>"

    Examples:
      | input | output |
      | 1 | 1 |
      | 1.1 | null |
      | -1 | -1 |
      | -1.1 | null |
      | 0 | 0 |
      | 0xF | 15 |
      | 128.243.48.6 | null |
      | 017 | 15 |

  Scenario Outline: Verifying ip address input
    Given I clean "<input>" as "IP_ADDRESS"
    Then the clean result should be "<output>"

    Examples:
      | input | output |
      | 128.243.48.6 | 128.243.48.6 |
      | 10.158.128.157 | 10.158.128.157 |
      | 125.3 | null |
      | 128.243.48.257 | null |
      | 0.0.0.0 | 0.0.0.0 |
      | text | null |
      | 2001:0db8:0a0b:12f0:0000:0000:0000:0001 | 2001:0db8:0a0b:12f0:0000:0000:0000:0001 |
      | 2001:db8:a0b:12f0::1 | 2001:db8:a0b:12f0::1 |

  Scenario Outline: Verifying raw input in unchanged
    Given I clean "<input>" as "RAW"
    Then the clean result should be "<output>"

    Examples:
      | input | output |
      | <p style='color: red'>Test text</p> | <p style='color: red'>Test text</p> |
      | Test > text | Test > text |
      | Test <script><!-- alert(\'Test\') --></script> text | Test <script><!-- alert(\'Test\') --></script> text |
      | http://example.com | http://example.com |
      | 0 | 0 |
      | 0xF | 0xF |
      | 128.243.48.6 | 128.243.48.6 |
      | 017 | 017 |
      | Café | Café |
      | æìřčžšíýúáÚůú | æìřčžšíýúáÚůú |

  Scenario Outline: Verifying text input
    Given I clean "<input>" as "TEXT"
    Then the clean result should be "<output>"

    Examples:
      | input | output |
      | <p style='color: red'>Test text</p> | Test text |
      | Test > text | Test > text |
      | Test <script><!-- alert(\'Test\') --></script> text | Test  text |
      | http://example.com | http://example.com |
      | 0 | 0 |
      | 0xF | 0xF |
      | 128.243.48.6 | 128.243.48.6 |
      | 017 | 017 |
      | Café | Café |
      | æìřčžšíýúáÚůú | æìřčžšíýúáÚůú |

  Scenario Outline: Verifying url input
    Given I clean "<input>" as "URL"
    Then the clean result should be "<output>"

    Examples:
      | input | output |
      | <p style='color: red'>Test text</p> | null |
      | Test > text | null |
      | http://example.com | http://example.com |
      | http://www.example.com/ | http://www.example.com/ |
      | https://www.example.com/ | https://www.example.com/ |
      | https://www.example.com/subdir/ | https://www.example.com/subdir/ |
      | https://www.example.com/page.html | https://www.example.com/page.html |
      | ftp://www.example.com/ | ftp://www.example.com/ |
      | 128.243.48.6 | null |
      | www.example.com | null |
      | https://www.example.com/page.php?id=4&page=first | https://www.example.com/page.php?id=4&page=first |
