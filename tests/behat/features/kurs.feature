Feature: Kurs

Scenario: ambil data kurs berdasarkan id
    When I request "GET /kurs/2"
    Then I get "200" response
    And scope into the "data" property
        And the properties exist:
            """
            id
            kode_valas
            kurs_idr
            jenis
            tanggal_awal
            tanggal_akhir
            """
        And the "id" property is an integer

Scenario: ambil data kurs per tanggal
    When I request "GET /kurs/2019-01-01"
    Then I get "200" response
    And the "data" property is an array
    And the "data" property contains at least 1 item

Scenario: ambil data kurs yang gk ada, kurs rupiah harus ada selamanya
    When I request "GET /kurs/9999-01-01"
    Then I get "200" response
    And the "data" property contains exactly 1 item

Scenario: ambil data kurs berdasarkan id yg gk valid
    When I request "GET /kurs/333"
    Then I get "404" response

Scenario: ambil data kurs pake query string, test kembalian kdu array walo kosong
    When I request "GET /kurs?kode=USD&number=5&tanggal=2019-08-02"
    Then I get "200" response
    And the properties exist:
        """
        data
        meta
        """
        And the "data" property is an array

Scenario: input data kurs tanpa credential
    Given I have the payload:
        """
        {
            "kode_valas": "CNY",
            "jenis": "KURS_BI",
            "kurs_idr": 18392.3195,
            "tanggal_awal": "2019-08-29",
            "tanggal_akhir": "2019-09-29"
        }
        """
    When I request "POST /kurs"
    Then I get "401" response

Scenario: input data kurs pake credential palsu
    Given I use the token "token_bapuk"
    And I have the payload:
        """
        {
            "kode_valas": "AUD",
            "jenis": "KURS_BI",
            "kurs_idr": 8392.3195,
            "tanggal_awal": "2019-09-29",
            "tanggal_akhir": "2019-09-29"
        }
        """
    When I request "POST /kurs"
    Then I get "401" response

Scenario: input data kurs dengan credential yang valid
    Given I use the token "token_pdtt"
    And I have the payload:
        """
        {
            "kode_valas": "SGD",
            "jenis": "KURS_BI",
            "kurs_idr": 4392.23,
            "tanggal_awal": "2019-09-29",
            "tanggal_akhir": "2019-09-29"
        }
        """
    When I request "POST /kurs"
    Then I get "200" response