UPDATE config SET config_value = '8' WHERE (config_key = 'DATABASE_VERSION');

INSERT INTO config (config_id, config_lastedited, config_lasteditedby, config_key, config_value, config_category, config_description, config_type, config_options, config_editable, config_required, config_added_to_dictionary) 
VALUES (NULL, '2019-02-19 21:25:16', '1', 'JAMS_TO_LOAD', '25', 'JAM_SETTINGS', 'Number of jams to load on the main page by default', 'NUMBER', '[]', '1', '1', '1');