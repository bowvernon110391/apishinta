Feature: Kurs

Scenario: ambil data kurs berdasarkan id
    When I request "GET sds /kurs/2"
    Then I get "200" response
    And scope into the "data" property
        And the properties exist:
            """
            id
            kode_valas
            nilai
            jenis
            tanggal_awal
            tanggal_akhir
            """
        And the "id" property is an integer

Scenario: ambil data kurs per tanggal
    When I request "GET /kurs/2019-01-01"
    Then I get "200" response
    And scope into the "data" property
        And the "data" property is an array

Scenario: ambil data kurs yang gk ada
    When I request "GET /kurs/9999-01-01"
    Then I get "200" response
    And the "data" property contains at least 1 item
    And scope into the "data" property
        And the "links" property contains 1 item

Scenario: input data kurs tanpa credential
    Given I have the payload:
        """
        {
            "kode_valas"    : "MYR",
            "nilai"         : 2500.0
        }
        """
    When I request "POST /kurs"
    Then I get "401" response

Scenario: input data kurs pake credential palsu
    Given I have the payload:
        """
        {
            "kode_valas"    : "MYR",
            "nilai"         : 2500.0
        }
        """
    Given I use the token "token_palsu"
    When I request "POST /kurs"
    Then I get "403" response