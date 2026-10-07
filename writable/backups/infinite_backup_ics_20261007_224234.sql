-- =====================================================
-- INFINITE DATABASE BACKUP
-- Selected Tables Only
-- Generated: 2026-10-07 22:42:31
-- =====================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- =====================================================
-- Table: dt01_ics_source_document
-- =====================================================

DROP TABLE IF EXISTS `dt01_ics_source_document`;

CREATE TABLE `dt01_ics_source_document` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `DOC_CODE` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `TITLE` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `VERSION` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `STATUS` enum('DRAFT','FINAL','SUPERSEDED','WITHDRAWN') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'DRAFT',
  `ISSUED_DATE` date DEFAULT NULL,
  `EFFECTIVE_DATE` date DEFAULT NULL,
  `PUBLISHER` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `IS_ACTIVE` tinyint(1) NOT NULL DEFAULT 0,
  `LANGUAGE_CODE` char(2) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'id',
  `ICD10_EDITION` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ICD9CM_EDITION` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `TOTAL_PAGES` int(11) DEFAULT NULL,
  `PDF_PAGE_OFFSET` int(11) NOT NULL DEFAULT 0,
  `SOURCE_FILE_NAME` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `SOURCE_FILE_SHA256` char(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `SUPERSEDES_ID` int(10) unsigned DEFAULT NULL,
  `NOTES` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `CREATED_AT` datetime NOT NULL DEFAULT current_timestamp(),
  `CREATED_BY` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `UPDATED_AT` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  `UPDATED_BY` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `UQ_DOC` (`DOC_CODE`,`VERSION`),
  KEY `IDX_DOC_ACTIVE` (`DOC_CODE`,`IS_ACTIVE`),
  KEY `FK_DOC_SUPERSEDES` (`SUPERSEDES_ID`),
  CONSTRAINT `FK_DOC_SUPERSEDES` FOREIGN KEY (`SUPERSEDES_ID`) REFERENCES `dt01_ics_source_document` (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `dt01_ics_source_document` (`ID`, `DOC_CODE`, `TITLE`, `VERSION`, `STATUS`, `ISSUED_DATE`, `EFFECTIVE_DATE`, `PUBLISHER`, `IS_ACTIVE`, `LANGUAGE_CODE`, `ICD10_EDITION`, `ICD9CM_EDITION`, `TOTAL_PAGES`, `PDF_PAGE_OFFSET`, `SOURCE_FILE_NAME`, `SOURCE_FILE_SHA256`, `SUPERSEDES_ID`, `NOTES`, `CREATED_AT`, `CREATED_BY`, `UPDATED_AT`, `UPDATED_BY`) VALUES ('1', 'ICS_IDRG', 'Indonesian Coding Standard (ICS) iDRG', 'V1', 'DRAFT', '2025-07-25', NULL, 'Kementerian Kesehatan RI - Pusat Pembiayaan Kesehatan', '1', 'id', 'ICD-10 WHO 2010 + IM', 'ICD-9-CM 2010 + IM', '483', '1', 'Salinan ICS Version 1_25072025 (1).pdf', NULL, NULL, 'Draft V1. Hak cipta Pusat Pembiayaan Kesehatan Kemenkes RI.', '2026-10-07 22:23:32', NULL, '2026-10-07 22:23:47', NULL);

-- =====================================================
-- Table: dt01_ics_mdc
-- =====================================================

DROP TABLE IF EXISTS `dt01_ics_mdc`;

CREATE TABLE `dt01_ics_mdc` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `DOCUMENT_ID` int(10) unsigned NOT NULL,
  `MDC_CODE` char(2) COLLATE utf8mb4_unicode_ci NOT NULL,
  `SORT_ORDER` int(11) NOT NULL DEFAULT 0,
  `IS_ACTIVE` tinyint(1) NOT NULL DEFAULT 1,
  `NAME_EN` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `NAME_ID` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `CHAPTER_NO` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `PAGE_START` int(11) DEFAULT NULL,
  `PAGE_END` int(11) DEFAULT NULL,
  `CREATED_BY` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `UPDATED_BY` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `CREATED_AT` datetime NOT NULL DEFAULT current_timestamp(),
  `UPDATED_AT` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`ID`),
  UNIQUE KEY `UQ_MDC_CODE` (`DOCUMENT_ID`,`MDC_CODE`),
  UNIQUE KEY `UQ_MDC_SORT` (`DOCUMENT_ID`,`SORT_ORDER`),
  KEY `IDX_MDC_DOCUMENT` (`DOCUMENT_ID`),
  KEY `IDX_MDC_CODE` (`MDC_CODE`),
  KEY `IDX_MDC_ACTIVE` (`DOCUMENT_ID`,`IS_ACTIVE`),
  CONSTRAINT `FK_MDC_DOC` FOREIGN KEY (`DOCUMENT_ID`) REFERENCES `dt01_ics_source_document` (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `dt01_ics_mdc` (`ID`, `DOCUMENT_ID`, `MDC_CODE`, `SORT_ORDER`, `IS_ACTIVE`, `NAME_EN`, `NAME_ID`, `CHAPTER_NO`, `PAGE_START`, `PAGE_END`, `CREATED_BY`, `UPDATED_BY`, `CREATED_AT`, `UPDATED_AT`) VALUES ('1', '1', '11', '1', '1', 'Diseases and Disorders of the Nervous System', 'SISTEM SYARAF', '3.1', '66', '79', '80b49d3a-71cf-11f1-b543-3a52e86ddc6f', NULL, '2026-10-07 22:30:52', '2026-10-07 22:35:23');
INSERT INTO `dt01_ics_mdc` (`ID`, `DOCUMENT_ID`, `MDC_CODE`, `SORT_ORDER`, `IS_ACTIVE`, `NAME_EN`, `NAME_ID`, `CHAPTER_NO`, `PAGE_START`, `PAGE_END`, `CREATED_BY`, `UPDATED_BY`, `CREATED_AT`, `UPDATED_AT`) VALUES ('2', '1', '12', '2', '1', 'Diseases and Disorders of the Eye and Adnexa', 'MATA & ADNEXA', '3.2', '0', '0', '80b49d3a-71cf-11f1-b543-3a52e86ddc6f', NULL, '2026-10-07 22:30:52', '2026-10-07 22:37:35');
INSERT INTO `dt01_ics_mdc` (`ID`, `DOCUMENT_ID`, `MDC_CODE`, `SORT_ORDER`, `IS_ACTIVE`, `NAME_EN`, `NAME_ID`, `CHAPTER_NO`, `PAGE_START`, `PAGE_END`, `CREATED_BY`, `UPDATED_BY`, `CREATED_AT`, `UPDATED_AT`) VALUES ('3', '1', '13', '3', '1', 'Diseases and Disorders of the Ear, Nose, Mouth and Throat', 'TELINGA, MULUT, TENGGOROKAN', '3.3', '0', '0', '80b49d3a-71cf-11f1-b543-3a52e86ddc6f', NULL, '2026-10-07 22:30:52', '2026-10-07 22:37:35');
INSERT INTO `dt01_ics_mdc` (`ID`, `DOCUMENT_ID`, `MDC_CODE`, `SORT_ORDER`, `IS_ACTIVE`, `NAME_EN`, `NAME_ID`, `CHAPTER_NO`, `PAGE_START`, `PAGE_END`, `CREATED_BY`, `UPDATED_BY`, `CREATED_AT`, `UPDATED_AT`) VALUES ('4', '1', '14', '4', '1', 'Diseases and Disorders of the Respiratory System', 'SISTEM PERNAFASAN', '3.4', '0', '0', '80b49d3a-71cf-11f1-b543-3a52e86ddc6f', NULL, '2026-10-07 22:30:52', '2026-10-07 22:37:35');
INSERT INTO `dt01_ics_mdc` (`ID`, `DOCUMENT_ID`, `MDC_CODE`, `SORT_ORDER`, `IS_ACTIVE`, `NAME_EN`, `NAME_ID`, `CHAPTER_NO`, `PAGE_START`, `PAGE_END`, `CREATED_BY`, `UPDATED_BY`, `CREATED_AT`, `UPDATED_AT`) VALUES ('5', '1', '15', '5', '1', 'Diseases and Disorders of the Circulatory System', 'JANTUNG DAN PEMBULUH DARAH', '3.5', '0', '0', '80b49d3a-71cf-11f1-b543-3a52e86ddc6f', NULL, '2026-10-07 22:30:52', '2026-10-07 22:37:35');
INSERT INTO `dt01_ics_mdc` (`ID`, `DOCUMENT_ID`, `MDC_CODE`, `SORT_ORDER`, `IS_ACTIVE`, `NAME_EN`, `NAME_ID`, `CHAPTER_NO`, `PAGE_START`, `PAGE_END`, `CREATED_BY`, `UPDATED_BY`, `CREATED_AT`, `UPDATED_AT`) VALUES ('6', '1', '16', '6', '1', 'Diseases and Disorders of the Digestive System', 'SALURAN PENCERNAAN', '3.6', '0', '0', '80b49d3a-71cf-11f1-b543-3a52e86ddc6f', NULL, '2026-10-07 22:30:52', '2026-10-07 22:37:35');
INSERT INTO `dt01_ics_mdc` (`ID`, `DOCUMENT_ID`, `MDC_CODE`, `SORT_ORDER`, `IS_ACTIVE`, `NAME_EN`, `NAME_ID`, `CHAPTER_NO`, `PAGE_START`, `PAGE_END`, `CREATED_BY`, `UPDATED_BY`, `CREATED_AT`, `UPDATED_AT`) VALUES ('7', '1', '17', '7', '1', 'Diseases and Disorders of the Hepatobiliary System and Pancreas', 'HEPATOBILIER DAN PANKREAS', '3.7', '0', '0', '80b49d3a-71cf-11f1-b543-3a52e86ddc6f', NULL, '2026-10-07 22:30:52', '2026-10-07 22:37:35');
INSERT INTO `dt01_ics_mdc` (`ID`, `DOCUMENT_ID`, `MDC_CODE`, `SORT_ORDER`, `IS_ACTIVE`, `NAME_EN`, `NAME_ID`, `CHAPTER_NO`, `PAGE_START`, `PAGE_END`, `CREATED_BY`, `UPDATED_BY`, `CREATED_AT`, `UPDATED_AT`) VALUES ('8', '1', '18', '8', '1', 'Diseases and Disorders of the Musculoskeletal System and Connective Tissue', 'MUSKULOSKELETAL DAN JARINGAN IKAT', '3.8', '0', '0', '80b49d3a-71cf-11f1-b543-3a52e86ddc6f', NULL, '2026-10-07 22:30:52', '2026-10-07 22:37:35');
INSERT INTO `dt01_ics_mdc` (`ID`, `DOCUMENT_ID`, `MDC_CODE`, `SORT_ORDER`, `IS_ACTIVE`, `NAME_EN`, `NAME_ID`, `CHAPTER_NO`, `PAGE_START`, `PAGE_END`, `CREATED_BY`, `UPDATED_BY`, `CREATED_AT`, `UPDATED_AT`) VALUES ('9', '1', '19', '9', '1', 'Diseases and Disorders of the Skin, Subcutaneous Tissue and Breast', 'KULIT DAN SUBKUTAN', '3.9', '0', '0', '80b49d3a-71cf-11f1-b543-3a52e86ddc6f', NULL, '2026-10-07 22:30:52', '2026-10-07 22:37:35');
INSERT INTO `dt01_ics_mdc` (`ID`, `DOCUMENT_ID`, `MDC_CODE`, `SORT_ORDER`, `IS_ACTIVE`, `NAME_EN`, `NAME_ID`, `CHAPTER_NO`, `PAGE_START`, `PAGE_END`, `CREATED_BY`, `UPDATED_BY`, `CREATED_AT`, `UPDATED_AT`) VALUES ('10', '1', '20', '10', '1', 'Endocrine, Nutritional and Metabolic Diseases and Disorders', 'ENDOKRIN, NUTRISI DAN METABOLIK', '3.10', '0', '0', '80b49d3a-71cf-11f1-b543-3a52e86ddc6f', NULL, '2026-10-07 22:30:52', '2026-10-07 22:37:35');
INSERT INTO `dt01_ics_mdc` (`ID`, `DOCUMENT_ID`, `MDC_CODE`, `SORT_ORDER`, `IS_ACTIVE`, `NAME_EN`, `NAME_ID`, `CHAPTER_NO`, `PAGE_START`, `PAGE_END`, `CREATED_BY`, `UPDATED_BY`, `CREATED_AT`, `UPDATED_AT`) VALUES ('11', '1', '21', '11', '1', 'Diseases and Disorders of the Kidney and Urinary Tract', 'DISEASE AND DISORDERS OF THE KIDNEY AND URINARY TRACT', '3.11', '0', '0', '80b49d3a-71cf-11f1-b543-3a52e86ddc6f', NULL, '2026-10-07 22:30:52', '2026-10-07 22:37:35');
INSERT INTO `dt01_ics_mdc` (`ID`, `DOCUMENT_ID`, `MDC_CODE`, `SORT_ORDER`, `IS_ACTIVE`, `NAME_EN`, `NAME_ID`, `CHAPTER_NO`, `PAGE_START`, `PAGE_END`, `CREATED_BY`, `UPDATED_BY`, `CREATED_AT`, `UPDATED_AT`) VALUES ('12', '1', '22', '12', '1', 'Diseases and Disorders of the Male Reproductive System', 'SISTEM REPRODUKSI PRIA', '3.12', '0', '0', '80b49d3a-71cf-11f1-b543-3a52e86ddc6f', NULL, '2026-10-07 22:30:52', '2026-10-07 22:37:35');
INSERT INTO `dt01_ics_mdc` (`ID`, `DOCUMENT_ID`, `MDC_CODE`, `SORT_ORDER`, `IS_ACTIVE`, `NAME_EN`, `NAME_ID`, `CHAPTER_NO`, `PAGE_START`, `PAGE_END`, `CREATED_BY`, `UPDATED_BY`, `CREATED_AT`, `UPDATED_AT`) VALUES ('13', '1', '23', '13', '1', 'Diseases and Disorders of the Female Reproductive System', 'SISTEM REPRODUKSI WANITA', '3.13', '0', '0', '80b49d3a-71cf-11f1-b543-3a52e86ddc6f', NULL, '2026-10-07 22:30:52', '2026-10-07 22:37:35');
INSERT INTO `dt01_ics_mdc` (`ID`, `DOCUMENT_ID`, `MDC_CODE`, `SORT_ORDER`, `IS_ACTIVE`, `NAME_EN`, `NAME_ID`, `CHAPTER_NO`, `PAGE_START`, `PAGE_END`, `CREATED_BY`, `UPDATED_BY`, `CREATED_AT`, `UPDATED_AT`) VALUES ('14', '1', '24', '14', '1', 'Pregnancy, Childbirth and the Puerperium', 'KEHAMILAN, PERSALINAN DAN NIFAS', '3.14', '0', '0', '80b49d3a-71cf-11f1-b543-3a52e86ddc6f', NULL, '2026-10-07 22:30:52', '2026-10-07 22:37:35');
INSERT INTO `dt01_ics_mdc` (`ID`, `DOCUMENT_ID`, `MDC_CODE`, `SORT_ORDER`, `IS_ACTIVE`, `NAME_EN`, `NAME_ID`, `CHAPTER_NO`, `PAGE_START`, `PAGE_END`, `CREATED_BY`, `UPDATED_BY`, `CREATED_AT`, `UPDATED_AT`) VALUES ('15', '1', '25', '15', '1', 'Newborns and Other Neonates with Conditions Originating in the Perinatal Period', 'PERINATOLOGI', '3.15', '0', '0', '80b49d3a-71cf-11f1-b543-3a52e86ddc6f', NULL, '2026-10-07 22:30:52', '2026-10-07 22:37:35');
INSERT INTO `dt01_ics_mdc` (`ID`, `DOCUMENT_ID`, `MDC_CODE`, `SORT_ORDER`, `IS_ACTIVE`, `NAME_EN`, `NAME_ID`, `CHAPTER_NO`, `PAGE_START`, `PAGE_END`, `CREATED_BY`, `UPDATED_BY`, `CREATED_AT`, `UPDATED_AT`) VALUES ('16', '1', '26', '16', '1', 'Diseases and Disorders of the Blood and Blood-Forming Organs', 'PENYAKIT PADA DARAH DAN ORGAN PEMBENTUK DARAH', '3.16', '0', '0', '80b49d3a-71cf-11f1-b543-3a52e86ddc6f', NULL, '2026-10-07 22:30:52', '2026-10-07 22:37:35');
INSERT INTO `dt01_ics_mdc` (`ID`, `DOCUMENT_ID`, `MDC_CODE`, `SORT_ORDER`, `IS_ACTIVE`, `NAME_EN`, `NAME_ID`, `CHAPTER_NO`, `PAGE_START`, `PAGE_END`, `CREATED_BY`, `UPDATED_BY`, `CREATED_AT`, `UPDATED_AT`) VALUES ('17', '1', '27', '17', '1', 'Myeloproliferative Diseases and Disorders', 'PENYAKIT DAN GANGGUAN PROLIFERATIF', '3.17', '0', '0', '80b49d3a-71cf-11f1-b543-3a52e86ddc6f', NULL, '2026-10-07 22:30:52', '2026-10-07 22:37:35');
INSERT INTO `dt01_ics_mdc` (`ID`, `DOCUMENT_ID`, `MDC_CODE`, `SORT_ORDER`, `IS_ACTIVE`, `NAME_EN`, `NAME_ID`, `CHAPTER_NO`, `PAGE_START`, `PAGE_END`, `CREATED_BY`, `UPDATED_BY`, `CREATED_AT`, `UPDATED_AT`) VALUES ('18', '1', '28', '18', '1', 'Infectious and Parasitic Diseases and Disorders', 'PENYAKIT INFEKSI DAN PARASIT', '3.18', '0', '0', '80b49d3a-71cf-11f1-b543-3a52e86ddc6f', NULL, '2026-10-07 22:30:52', '2026-10-07 22:37:35');
INSERT INTO `dt01_ics_mdc` (`ID`, `DOCUMENT_ID`, `MDC_CODE`, `SORT_ORDER`, `IS_ACTIVE`, `NAME_EN`, `NAME_ID`, `CHAPTER_NO`, `PAGE_START`, `PAGE_END`, `CREATED_BY`, `UPDATED_BY`, `CREATED_AT`, `UPDATED_AT`) VALUES ('19', '1', '29', '19', '1', 'Diseases and Disorders of the Circulatory System', 'PENYAKIT DAN GANGGUAN MENTAL', '3.19', '0', '0', '80b49d3a-71cf-11f1-b543-3a52e86ddc6f', NULL, '2026-10-07 22:30:52', '2026-10-07 22:37:35');
INSERT INTO `dt01_ics_mdc` (`ID`, `DOCUMENT_ID`, `MDC_CODE`, `SORT_ORDER`, `IS_ACTIVE`, `NAME_EN`, `NAME_ID`, `CHAPTER_NO`, `PAGE_START`, `PAGE_END`, `CREATED_BY`, `UPDATED_BY`, `CREATED_AT`, `UPDATED_AT`) VALUES ('20', '1', '30', '20', '1', 'Factors Influencing Health Status and Other Contacts with Health Services', 'FAKTOR YANG MEMPENGARUHI STATUS KESEHATAN', '3.20', '0', '0', '80b49d3a-71cf-11f1-b543-3a52e86ddc6f', NULL, '2026-10-07 22:30:52', '2026-10-07 22:37:35');
INSERT INTO `dt01_ics_mdc` (`ID`, `DOCUMENT_ID`, `MDC_CODE`, `SORT_ORDER`, `IS_ACTIVE`, `NAME_EN`, `NAME_ID`, `CHAPTER_NO`, `PAGE_START`, `PAGE_END`, `CREATED_BY`, `UPDATED_BY`, `CREATED_AT`, `UPDATED_AT`) VALUES ('21', '1', '31', '21', '1', 'Multiple Significant Trauma', 'MULTIPLE SIGNIFICANT TRAUMA', '3.21', '0', '0', '80b49d3a-71cf-11f1-b543-3a52e86ddc6f', NULL, '2026-10-07 22:30:52', '2026-10-07 22:37:35');
INSERT INTO `dt01_ics_mdc` (`ID`, `DOCUMENT_ID`, `MDC_CODE`, `SORT_ORDER`, `IS_ACTIVE`, `NAME_EN`, `NAME_ID`, `CHAPTER_NO`, `PAGE_START`, `PAGE_END`, `CREATED_BY`, `UPDATED_BY`, `CREATED_AT`, `UPDATED_AT`) VALUES ('22', '1', '32', '22', '1', 'Burns', 'LUKA BAKAR', '3.22', '0', '0', '80b49d3a-71cf-11f1-b543-3a52e86ddc6f', NULL, '2026-10-07 22:30:52', '2026-10-07 22:37:35');
INSERT INTO `dt01_ics_mdc` (`ID`, `DOCUMENT_ID`, `MDC_CODE`, `SORT_ORDER`, `IS_ACTIVE`, `NAME_EN`, `NAME_ID`, `CHAPTER_NO`, `PAGE_START`, `PAGE_END`, `CREATED_BY`, `UPDATED_BY`, `CREATED_AT`, `UPDATED_AT`) VALUES ('23', '1', '33', '23', '1', 'Injuries, Poisoning and Certain Other Consequences of External Causes', 'INJURIES, POISONING & CERTAIN OTHER CONSEQUENCES OF EXTERNAL CAUSES', '3.23', '0', '0', '80b49d3a-71cf-11f1-b543-3a52e86ddc6f', NULL, '2026-10-07 22:30:52', '2026-10-07 22:37:35');
INSERT INTO `dt01_ics_mdc` (`ID`, `DOCUMENT_ID`, `MDC_CODE`, `SORT_ORDER`, `IS_ACTIVE`, `NAME_EN`, `NAME_ID`, `CHAPTER_NO`, `PAGE_START`, `PAGE_END`, `CREATED_BY`, `UPDATED_BY`, `CREATED_AT`, `UPDATED_AT`) VALUES ('24', '1', '34', '24', '1', 'Neoplasms', 'NEOPLASMA', '3.24', '0', '0', '80b49d3a-71cf-11f1-b543-3a52e86ddc6f', NULL, '2026-10-07 22:30:52', '2026-10-07 22:37:35');
INSERT INTO `dt01_ics_mdc` (`ID`, `DOCUMENT_ID`, `MDC_CODE`, `SORT_ORDER`, `IS_ACTIVE`, `NAME_EN`, `NAME_ID`, `CHAPTER_NO`, `PAGE_START`, `PAGE_END`, `CREATED_BY`, `UPDATED_BY`, `CREATED_AT`, `UPDATED_AT`) VALUES ('25', '1', '35', '25', '1', 'Rehabilitation', 'REHABILITASI MEDIS', '3.25', '0', '0', '80b49d3a-71cf-11f1-b543-3a52e86ddc6f', NULL, '2026-10-07 22:30:52', '2026-10-07 22:37:35');
INSERT INTO `dt01_ics_mdc` (`ID`, `DOCUMENT_ID`, `MDC_CODE`, `SORT_ORDER`, `IS_ACTIVE`, `NAME_EN`, `NAME_ID`, `CHAPTER_NO`, `PAGE_START`, `PAGE_END`, `CREATED_BY`, `UPDATED_BY`, `CREATED_AT`, `UPDATED_AT`) VALUES ('26', '1', '36', '26', '1', 'Ungroupable or Unrelated', 'UNGROUPABLE ATAU UNRELATED', '3.26', '0', '0', '80b49d3a-71cf-11f1-b543-3a52e86ddc6f', NULL, '2026-10-07 22:30:52', '2026-10-07 22:37:35');

-- =====================================================
-- Table: dt01_ics_ch3_section
-- =====================================================

DROP TABLE IF EXISTS `dt01_ics_ch3_section`;

CREATE TABLE `dt01_ics_ch3_section` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `DOCUMENT_ID` int(10) unsigned NOT NULL,
  `MDC_ID` int(10) unsigned NOT NULL,
  `PARENT_ID` int(10) unsigned DEFAULT NULL,
  `SECTION_NO` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `SECTION_TYPE` enum('GENERAL_NOTE','TOPIC','SUBTOPIC','PROCEDURE_TOPIC','ERROR_CODE') COLLATE utf8mb4_unicode_ci NOT NULL,
  `TITLE` varchar(300) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ICD_RANGE_TEXT` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `SORT_ORDER` int(11) NOT NULL,
  `PAGE_PRINTED` int(11) DEFAULT NULL,
  `PAGE_PDF` int(11) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `UQ_SECTION_ORDER` (`DOCUMENT_ID`,`MDC_ID`,`SORT_ORDER`),
  KEY `IDX_SECTION_MDC` (`MDC_ID`),
  KEY `IDX_SECTION_PARENT` (`PARENT_ID`),
  CONSTRAINT `FK_SEC_DOC` FOREIGN KEY (`DOCUMENT_ID`) REFERENCES `dt01_ics_source_document` (`ID`),
  CONSTRAINT `FK_SEC_MDC` FOREIGN KEY (`MDC_ID`) REFERENCES `dt01_ics_mdc` (`ID`),
  CONSTRAINT `FK_SEC_PARENT` FOREIGN KEY (`PARENT_ID`) REFERENCES `dt01_ics_ch3_section` (`ID`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `dt01_ics_ch3_section` (`ID`, `DOCUMENT_ID`, `MDC_ID`, `PARENT_ID`, `SECTION_NO`, `SECTION_TYPE`, `TITLE`, `ICD_RANGE_TEXT`, `SORT_ORDER`, `PAGE_PRINTED`, `PAGE_PDF`) VALUES ('1', '1', '1', NULL, '3.1.1', 'TOPIC', 'Meningitis', '(G00-G03)', '1', '66', NULL);
INSERT INTO `dt01_ics_ch3_section` (`ID`, `DOCUMENT_ID`, `MDC_ID`, `PARENT_ID`, `SECTION_NO`, `SECTION_TYPE`, `TITLE`, `ICD_RANGE_TEXT`, `SORT_ORDER`, `PAGE_PRINTED`, `PAGE_PDF`) VALUES ('2', '1', '1', NULL, '3.1.2', 'TOPIC', 'Encephalitis And Myelitis', '(G04-G05)', '2', '66', NULL);

-- =====================================================
-- Table: dt01_ics_ch3_section_icd_scope
-- =====================================================

DROP TABLE IF EXISTS `dt01_ics_ch3_section_icd_scope`;

CREATE TABLE `dt01_ics_ch3_section_icd_scope` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `SECTION_ID` int(10) unsigned NOT NULL,
  `CODE_FROM` varchar(12) COLLATE utf8mb4_unicode_ci NOT NULL,
  `CODE_TO` varchar(12) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `CODE_SYSTEM` enum('ICD10_2010','ICD9CM_2010') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ICD10_2010',
  PRIMARY KEY (`ID`),
  KEY `IDX_SCOPE_CODES` (`CODE_FROM`,`CODE_TO`),
  KEY `FK_SCOPE_SEC` (`SECTION_ID`),
  CONSTRAINT `FK_SCOPE_SEC` FOREIGN KEY (`SECTION_ID`) REFERENCES `dt01_ics_ch3_section` (`ID`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `dt01_ics_ch3_section_icd_scope` (`ID`, `SECTION_ID`, `CODE_FROM`, `CODE_TO`, `CODE_SYSTEM`) VALUES ('1', '1', 'G00', 'G03', 'ICD10_2010');
INSERT INTO `dt01_ics_ch3_section_icd_scope` (`ID`, `SECTION_ID`, `CODE_FROM`, `CODE_TO`, `CODE_SYSTEM`) VALUES ('2', '2', 'G04', 'G05', 'ICD10_2010');

-- =====================================================
-- Table: dt01_ics_coding_rule
-- =====================================================

DROP TABLE IF EXISTS `dt01_ics_coding_rule`;

CREATE TABLE `dt01_ics_coding_rule` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `SECTION_ID` int(10) unsigned NOT NULL,
  `RULE_SEQ` int(11) NOT NULL,
  `RULE_TYPE` enum('PRIMARY_DX','SECONDARY_DX','CODE_ALSO','EXTERNAL_CAUSE','SELECT_BY_CONDITION','UNSPECIFIED_FALLBACK','OMIT_CODE','DO_NOT_CODE_SEPARATELY','INPUT_PROCEDURE','DEFINITION','NOTE') COLLATE utf8mb4_unicode_ci NOT NULL,
  `CONDITION_TEXT` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `INSTRUCTION_TEXT` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `IS_MANDATORY` tinyint(1) DEFAULT NULL,
  `AGE_CONDITION` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `SEX_CONDITION` enum('L','P') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `SOURCE_TEXT` longtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `PAGE_PRINTED` int(11) DEFAULT NULL,
  `PAGE_PDF` int(11) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `UQ_RULE_SEQ` (`SECTION_ID`,`RULE_SEQ`),
  CONSTRAINT `FK_RULE_SEC` FOREIGN KEY (`SECTION_ID`) REFERENCES `dt01_ics_ch3_section` (`ID`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `dt01_ics_coding_rule` (`ID`, `SECTION_ID`, `RULE_SEQ`, `RULE_TYPE`, `CONDITION_TEXT`, `INSTRUCTION_TEXT`, `IS_MANDATORY`, `AGE_CONDITION`, `SEX_CONDITION`, `SOURCE_TEXT`, `PAGE_PRINTED`, `PAGE_PDF`) VALUES ('1', '1', '1', 'NOTE', 'Meningitis non infeksi akibat obat atau metastasis kanker.', 'Dikodekan sesuai dengan etiologi spesifik.', '1', NULL, NULL, 'Meningitis non infeksi akibat obat atau metastasis kanker harus dikodekan sesuai dengan etiologi spesifik.', '66', '67');
INSERT INTO `dt01_ics_coding_rule` (`ID`, `SECTION_ID`, `RULE_SEQ`, `RULE_TYPE`, `CONDITION_TEXT`, `INSTRUCTION_TEXT`, `IS_MANDATORY`, `AGE_CONDITION`, `SEX_CONDITION`, `SOURCE_TEXT`, `PAGE_PRINTED`, `PAGE_PDF`) VALUES ('2', '1', '2', 'SELECT_BY_CONDITION', 'Dokter mendiagnosis \"bacterial meningitis\". Jenis bakteri tidak diketahui: G00.9. Hasil pemeriksaan menunjukkan bakteri spesifik (mis. pneumococcal): G00.1. Tidak dilakukan pemeriksaan kultur: G03.9.', 'Gunakan G00.9 (Bacterial meningitis, unspecified) bila jenis bakteri tidak diketahui; G00.1 (Pneumococcal meningitis) bila bakteri spesifik teridentifikasi; G03.9 (Meningitis, unspecified) bila tidak dilakukan kultur.', '0', NULL, NULL, 'Jika dokter mendiagnosis \"bacterial meningitis\" tetapi tidak diketahui jenis bakterinya, maka dapat menggunakan kode G00.9 (Bacterial meningitis, unspecified). Namun, jika hasil pemeriksaan mengidentifikasi adanya jenis bakteri spesifik, misalnya pneumococcal, maka dapat menggunakan kode G00.1 (Pneumococcal meningitis). Jika tidak dilakukan pemeriksaan kultur, maka dapat menggunakan kode G03.9 (Meningitis, unspecified).', '66', '67');
INSERT INTO `dt01_ics_coding_rule` (`ID`, `SECTION_ID`, `RULE_SEQ`, `RULE_TYPE`, `CONDITION_TEXT`, `INSTRUCTION_TEXT`, `IS_MANDATORY`, `AGE_CONDITION`, `SEX_CONDITION`, `SOURCE_TEXT`, `PAGE_PRINTED`, `PAGE_PDF`) VALUES ('3', '2', '1', 'SECONDARY_DX', 'Myelitis viral (mis. cytomegalovirus).', 'Gunakan G05.1* (Encephalitis, myelitis, and encephalomyelitis in viral diseases classified elsewhere) sebagai diagnosis tambahan, dan kode virusnya sebagai diagnosis utama.', '0', NULL, NULL, 'Pada kasus myelitis viral (misalnya cytomegalovirus), dapat menggunakan kode G05.1* (Encephalitis, myelitis, and encephalomyelitis in viral diseases classified elsewhere) sebagai diagnosis tambahan dan kode virusnya sebagai diagnosis utama.', '66', '67');
INSERT INTO `dt01_ics_coding_rule` (`ID`, `SECTION_ID`, `RULE_SEQ`, `RULE_TYPE`, `CONDITION_TEXT`, `INSTRUCTION_TEXT`, `IS_MANDATORY`, `AGE_CONDITION`, `SEX_CONDITION`, `SOURCE_TEXT`, `PAGE_PRINTED`, `PAGE_PDF`) VALUES ('4', '2', '2', 'SELECT_BY_CONDITION', 'Dokter mendiagnosis \"viral transverse myelitis\". Virus penyebab tidak disebutkan: G04.3 (IM). Virus penyebab diketahui: B00.4† + G05.1*.', 'Gunakan G04.3 (Viral transverse myelitis, unspecified) (IM) bila virus tidak disebutkan; bila virus diketahui gunakan B00.4† (Herpesviral encephalitis) + G05.1* (Encephalitis, myelitis, and encephalomyelitis in viral diseases classified elsewhere).', '0', NULL, NULL, 'Pada kasus myelitis transverse viral, jika dokter mendiagnosis \"viral transverse myelitis\" tanpa menyebutkan jenis virusnya, maka dapat menggunakan kode G04.3 (Viral transverse myelitis, unspecified) (IM). Namun, jika virus penyebab diketahui, maka dapat menggunakan kode B00.4† (Herpesviral encephalitis) + G05.1* (Encephalitis, myelitis, and encephalomyelitis in viral diseases classified elsewhere).', '66', '67');

-- =====================================================
-- Table: dt01_ics_coding_rule_code
-- =====================================================

DROP TABLE IF EXISTS `dt01_ics_coding_rule_code`;

CREATE TABLE `dt01_ics_coding_rule_code` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `RULE_ID` int(10) unsigned NOT NULL,
  `ICD_ID` int(10) unsigned NOT NULL,
  `CODE_ROLE` enum('PRIMARY','SECONDARY','ADDITIONAL','CODE_ALSO','EXTERNAL_CAUSE','ETIOLOGY_DAGGER','MANIFESTATION_ASTERISK','PROCEDURE','NOT_TO_USE','ALTERNATIVE') COLLATE utf8mb4_unicode_ci NOT NULL,
  `SEQ_IN_RULE` int(11) NOT NULL DEFAULT 1,
  `NOTE` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `UQ_RULE_CODE` (`RULE_ID`,`ICD_ID`,`CODE_ROLE`,`SEQ_IN_RULE`),
  KEY `IDX_RULE_CODE_ICD` (`ICD_ID`),
  CONSTRAINT `FK_RC_ICD` FOREIGN KEY (`ICD_ID`) REFERENCES `dt01_icd_code` (`ID`),
  CONSTRAINT `FK_RC_RULE` FOREIGN KEY (`RULE_ID`) REFERENCES `dt01_ics_coding_rule` (`ID`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `dt01_ics_coding_rule_code` (`ID`, `RULE_ID`, `ICD_ID`, `CODE_ROLE`, `SEQ_IN_RULE`, `NOTE`) VALUES ('1', '1', '7', 'ALTERNATIVE', '1', 'Bacterial meningitis tetapi jenis bakteri tidak diketahui');
INSERT INTO `dt01_ics_coding_rule_code` (`ID`, `RULE_ID`, `ICD_ID`, `CODE_ROLE`, `SEQ_IN_RULE`, `NOTE`) VALUES ('2', '2', '3', 'ALTERNATIVE', '1', 'Digunakan jika hasil pemeriksaan mengidentifikasi bakteri pneumococcal');
INSERT INTO `dt01_ics_coding_rule_code` (`ID`, `RULE_ID`, `ICD_ID`, `CODE_ROLE`, `SEQ_IN_RULE`, `NOTE`) VALUES ('3', '3', '28', 'MANIFESTATION_ASTERISK', '1', 'Diagnosis tambahan untuk encephalitis, myelitis, dan encephalomyelitis pada penyakit virus yang diklasifikasikan di tempat lain');
INSERT INTO `dt01_ics_coding_rule_code` (`ID`, `RULE_ID`, `ICD_ID`, `CODE_ROLE`, `SEQ_IN_RULE`, `NOTE`) VALUES ('4', '4', '23', 'PRIMARY', '1', 'Viral transverse myelitis tanpa menyebutkan jenis virus');

-- =====================================================
-- Table: dt01_ics_case_example
-- =====================================================

DROP TABLE IF EXISTS `dt01_ics_case_example`;

CREATE TABLE `dt01_ics_case_example` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `SECTION_ID` int(10) unsigned NOT NULL,
  `RULE_ID` int(10) unsigned DEFAULT NULL,
  `EXAMPLE_LABEL` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `CASE_NARRATIVE` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `EXPECTED_IDRG_NOTE` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `PAGE_PRINTED` int(11) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `FK_EX_SEC` (`SECTION_ID`),
  KEY `FK_EX_RULE` (`RULE_ID`),
  CONSTRAINT `FK_EX_RULE` FOREIGN KEY (`RULE_ID`) REFERENCES `dt01_ics_coding_rule` (`ID`) ON DELETE SET NULL,
  CONSTRAINT `FK_EX_SEC` FOREIGN KEY (`SECTION_ID`) REFERENCES `dt01_ics_ch3_section` (`ID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =====================================================
-- Table: dt01_ics_case_example_item
-- =====================================================

DROP TABLE IF EXISTS `dt01_ics_case_example_item`;

CREATE TABLE `dt01_ics_case_example_item` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `EXAMPLE_ID` int(10) unsigned NOT NULL,
  `ITEM_ROLE` enum('DX_PRIMER','DX_SEKUNDER','PROSEDUR') COLLATE utf8mb4_unicode_ci NOT NULL,
  `SEQ` int(11) NOT NULL DEFAULT 1,
  `DOCTOR_DIAGNOSIS` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ICD_ID` int(10) unsigned DEFAULT NULL,
  `CODER_TEXT` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `FK_EXI_EX` (`EXAMPLE_ID`),
  KEY `FK_EXI_ICD` (`ICD_ID`),
  CONSTRAINT `FK_EXI_EX` FOREIGN KEY (`EXAMPLE_ID`) REFERENCES `dt01_ics_case_example` (`ID`) ON DELETE CASCADE,
  CONSTRAINT `FK_EXI_ICD` FOREIGN KEY (`ICD_ID`) REFERENCES `dt01_icd_code` (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =====================================================
-- Table: dt01_ics_idrg_dc
-- =====================================================

DROP TABLE IF EXISTS `dt01_ics_idrg_dc`;

CREATE TABLE `dt01_ics_idrg_dc` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `MDC_ID` int(10) unsigned NOT NULL,
  `DC_NAME` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `DC_CODE` varchar(5) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `UQ_DC` (`MDC_ID`,`DC_NAME`),
  CONSTRAINT `FK_DC_MDC` FOREIGN KEY (`MDC_ID`) REFERENCES `dt01_ics_mdc` (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =====================================================
-- Table: dt01_ics_special_code_group
-- =====================================================

DROP TABLE IF EXISTS `dt01_ics_special_code_group`;

CREATE TABLE `dt01_ics_special_code_group` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `SECTION_ID` int(10) unsigned NOT NULL,
  `TABLE_LABEL` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ROW_NO` int(11) DEFAULT NULL,
  `PROCEDURE_NAME` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `LOGIC_NOTE` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `FK_SCG_SEC` (`SECTION_ID`),
  CONSTRAINT `FK_SCG_SEC` FOREIGN KEY (`SECTION_ID`) REFERENCES `dt01_ics_ch3_section` (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =====================================================
-- Table: dt01_ics_special_code_group_item
-- =====================================================

DROP TABLE IF EXISTS `dt01_ics_special_code_group_item`;

CREATE TABLE `dt01_ics_special_code_group_item` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `GROUP_ID` int(10) unsigned NOT NULL,
  `ICD_ID` int(10) unsigned DEFAULT NULL,
  `SUB_GROUP` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `COMBO_EXPRESSION` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `IS_REQUIRED_COMBO` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`ID`),
  KEY `FK_SCGI_GRP` (`GROUP_ID`),
  KEY `FK_SCGI_ICD` (`ICD_ID`),
  CONSTRAINT `FK_SCGI_GRP` FOREIGN KEY (`GROUP_ID`) REFERENCES `dt01_ics_special_code_group` (`ID`) ON DELETE CASCADE,
  CONSTRAINT `FK_SCGI_ICD` FOREIGN KEY (`ICD_ID`) REFERENCES `dt01_icd_code` (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =====================================================
-- Table: dt01_ics_special_code_group_dc
-- =====================================================

DROP TABLE IF EXISTS `dt01_ics_special_code_group_dc`;

CREATE TABLE `dt01_ics_special_code_group_dc` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `GROUP_ID` int(10) unsigned NOT NULL,
  `DC_ID` int(10) unsigned NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `UQ_SCGDC` (`GROUP_ID`,`DC_ID`),
  KEY `FK_SCGD_DC` (`DC_ID`),
  CONSTRAINT `FK_SCGD_DC` FOREIGN KEY (`DC_ID`) REFERENCES `dt01_ics_idrg_dc` (`ID`),
  CONSTRAINT `FK_SCGD_GRP` FOREIGN KEY (`GROUP_ID`) REFERENCES `dt01_ics_special_code_group` (`ID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =====================================================
-- Table: dt01_ics_idrg_error_code
-- =====================================================

DROP TABLE IF EXISTS `dt01_ics_idrg_error_code`;

CREATE TABLE `dt01_ics_idrg_error_code` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `SECTION_ID` int(10) unsigned DEFAULT NULL,
  `ERROR_CODE` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ERROR_NAME` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `CAUSE_TEXT` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `RESOLUTION_TEXT` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `EXAMPLE_TEXT` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `HAS_SCREENSHOT` tinyint(1) NOT NULL DEFAULT 0,
  `PAGE_PRINTED` int(11) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `UQ_ERROR_CODE` (`ERROR_CODE`),
  KEY `FK_ERR_SEC` (`SECTION_ID`),
  CONSTRAINT `FK_ERR_SEC` FOREIGN KEY (`SECTION_ID`) REFERENCES `dt01_ics_ch3_section` (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =====================================================
-- Table: dt01_ics_idrg_error_required_code
-- =====================================================

DROP TABLE IF EXISTS `dt01_ics_idrg_error_required_code`;

CREATE TABLE `dt01_ics_idrg_error_required_code` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ERROR_ID` int(10) unsigned NOT NULL,
  `ICD_ID` int(10) unsigned NOT NULL,
  `REQUIREMENT_GROUP` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `UQ_ERR_REQ` (`ERROR_ID`,`ICD_ID`),
  KEY `FK_ERQ_ICD` (`ICD_ID`),
  CONSTRAINT `FK_ERQ_ERR` FOREIGN KEY (`ERROR_ID`) REFERENCES `dt01_ics_idrg_error_code` (`ID`) ON DELETE CASCADE,
  CONSTRAINT `FK_ERQ_ICD` FOREIGN KEY (`ICD_ID`) REFERENCES `dt01_icd_code` (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =====================================================
-- Table: dt01_ics_drug_external_cause_map
-- =====================================================

DROP TABLE IF EXISTS `dt01_ics_drug_external_cause_map`;

CREATE TABLE `dt01_ics_drug_external_cause_map` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `SECTION_ID` int(10) unsigned NOT NULL,
  `DRUG_CLASS` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `DRUG_EXAMPLES` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ROUTE_CONTEXT` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `POISONING_ICD_ID` int(10) unsigned DEFAULT NULL,
  `ADVERSE_EFFECT_ICD_ID` int(10) unsigned DEFAULT NULL,
  `INTENT_NOTE` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `FK_DEC_SEC` (`SECTION_ID`),
  KEY `FK_DEC_POI` (`POISONING_ICD_ID`),
  KEY `FK_DEC_ADV` (`ADVERSE_EFFECT_ICD_ID`),
  CONSTRAINT `FK_DEC_ADV` FOREIGN KEY (`ADVERSE_EFFECT_ICD_ID`) REFERENCES `dt01_icd_code` (`ID`),
  CONSTRAINT `FK_DEC_POI` FOREIGN KEY (`POISONING_ICD_ID`) REFERENCES `dt01_icd_code` (`ID`),
  CONSTRAINT `FK_DEC_SEC` FOREIGN KEY (`SECTION_ID`) REFERENCES `dt01_ics_ch3_section` (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


SET FOREIGN_KEY_CHECKS = 1;
