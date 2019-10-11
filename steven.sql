SELECT row_to_json( fc )
 FROM ( SELECT 'FeatureCollection' AS type, ARRAY_TO_JSON( ARRAY_AGG( f ) ) AS features
        FROM ( SELECT 'Feature' AS type
               , ST_ASGeoJSON( lg.geog ) AS geometry
               , row_to_json( lp ) AS properties
               FROM locations AS lg
               INNER JOIN ( SELECT loc_id, loc_name FROM locations ) AS lp
               ON lg.loc_id = lp.loc_id ) AS f ) AS fc;



SELECT /*TO_JSON_STRING*/( featureCollection )
FROM ( SELECT 'FeatureCollection' AS type, ARRAY_AGG( feature ) AS features
       FROM ( SELECT 'Feature' AS type,
              ( SELECT STRUCT( a AS type, b AS coordinates )
                FROM
                  ( SELECT REPLACE( JSON_EXTRACT( ST_ASGeoJSON( ST_GeogPoint( CAST( longitude AS FLOAT64 ), CAST( latitude AS FLOAT64 ) ) ), '$.type' ), '"', '' ) AS a,
                          JSON_EXTRACT( ST_ASGeoJSON( ST_GeogPoint( CAST( longitude AS FLOAT64 ), CAST( latitude AS FLOAT64 ) ) ), '$.coordinates' ) AS b) ) AS geometry,
              properties
              FROM `metal-celerity-196600.tasing_peru.properties` AS propGeog
              INNER JOIN ( SELECT id, bedrooms, bathrooms FROM `metal-celerity-196600.tasing_peru.properties` ) AS properties ON propGeog.id = properties.id
              ORDER BY publication_date DESC
              LIMIT 500 ) AS feature ) AS featureCollection;


ARRAY( SELECT * FROM UNNEST( SPLIT( SUBSTR( x, 2 , LENGTH( x ) - 2 ) ) ) ) AS steven,



------------------------------------------- geojson

SELECT /*TO_JSON_STRING*/( featureCollection )
FROM (
    SELECT 'FeatureCollection' AS type,
        ARRAY_AGG( feature ) AS features
    FROM (
        SELECT 'Feature' AS type,
        (
            SELECT STRUCT( type, coordinates )
            FROM (
                SELECT REPLACE( JSON_EXTRACT( ST_ASGeoJSON( ST_GeogPoint( CAST( longitude AS FLOAT64 ), CAST( latitude AS FLOAT64 ) ) ), '$.type' ), '"', '' ) AS type,
                    (
                        SELECT [ zero, one ]
                        FROM (
                            SELECT CAST( JSON_EXTRACT( ST_ASGeoJSON( ST_GeogPoint( CAST( longitude AS FLOAT64 ), CAST( latitude AS FLOAT64 ) ) ), '$.coordinates[0]' ) AS FLOAT64 ) AS zero,
                                CAST( JSON_EXTRACT( ST_ASGeoJSON( ST_GeogPoint( CAST( longitude AS FLOAT64 ), CAST( latitude AS FLOAT64 ) ) ), '$.coordinates[1]' ) AS FLOAT64 ) AS one
                        )
                    ) AS coordinates
            )
        ) AS geometry,
        (
            /* LIST OF PROPERTIES */
            SELECT STRUCT( id,
                property_type )
        ) AS properties
        FROM `metal-celerity-196600.tasing_peru.properties` AS outerTable
        -- WHERE
        ORDER BY properties.id DESC
        LIMIT 500
    ) AS feature
) AS featureCollection;


------------------------------------------- select con todo

    SELECT *
    FROM (
        (
            SELECT prop.id,
                prop.link,
                CAST( prop.antiquity_years AS INT64 ) AS antiquity_years,
                prop.bedrooms,
                prop.bathrooms,
                prop.parkings,
                CAST( prop.total_area_m2 AS FLOAT64 ) AS total_area_m2,
                CAST( prop.build_area_m2 AS FLOAT64 ) AS build_area_m2,
                prop.address,
                CAST( prop.publication_date AS STRING ) AS publication_date_formated,
                CONCAT( FORMAT( "%'d", CAST( prop.dollars_price AS INT64 ) ), SUBSTR( FORMAT( "%.2f", CAST( prop.dollars_price AS FLOAT64 ) ), -3 ) ) AS dollars_price,
                CONCAT( FORMAT( "%'d", CAST( prop.others_price AS INT64 ) ), SUBSTR( FORMAT( "%.2f", CAST( prop.others_price AS FLOAT64 ) ), -3 ) )   AS others_price,
                prop.property_type,
                prop.property_new,
                prop.longitude,
                prop.latitude,
                prop.image_list,
                prop.region_id,
                r.sub_reg3,
                prop.publication_type,
                'properties' AS table
            FROM       `metal-celerity-196600.tasing_peru.properties` prop
            INNER JOIN `metal-celerity-196600.tasing_peru.regions` r
            ON         r.id = prop.region_id
            ORDER BY   id DESC
        )
        UNION ALL
        (
            SELECT prop.id,
                p.link,
                0 AS antiquity_years,
                prop.bedrooms,
                prop.bathrooms,
                prop.parkings,
                CAST( prop.total_area_m2 AS FLOAT64 ) AS total_area_m2,
                CAST( prop.build_area_m2 AS FLOAT64 ) AS build_area_m2,
                p.address,
                CAST( p.publication_date AS STRING ) AS publication_date_formated,
                CONCAT( FORMAT( "%'d", CAST( prop.dollars_price AS INT64 ) ), SUBSTR( FORMAT( "%.2f", CAST( prop.dollars_price AS FLOAT64 ) ), -3 ) ) AS dollars_price,
                CONCAT( FORMAT( "%'d", CAST( prop.others_price AS INT64 ) ), SUBSTR( FORMAT( "%.2f", CAST( prop.others_price AS FLOAT64 ) ), -3 ) ) AS others_price,
                'Proyecto' AS property_type,
                true AS property_new,
                p.longitude,
                p.latitude,
                p.image_list,
                p.region_id,
                r.sub_reg3,
                1                    AS publication_type,
                'project_properties' AS TABLE
            FROM       `metal-celerity-196600.tasing_peru.project_properties` prop
            INNER JOIN `metal-celerity-196600.tasing_peru.projects` p
            ON         p.id = prop.project_id
            INNER JOIN `metal-celerity-196600.tasing_peru.regions` r
            ON         r.id = p.region_id
            ORDER BY   id DESC
        )
    ) AS x
    WHERE     CAST( longitude AS FLOAT64 ) <= -77.026155299722
    AND       CAST( longitude AS FLOAT64 ) >= -77.029974765359
    AND       CAST( latitude AS FLOAT64 ) <= -12.048018138883
    AND       CAST( latitude AS FLOAT64 ) >= -12.05208920271
    AND       ST_WITHIN(ST_GEOGPOINT( CAST( longitude AS FLOAT64 ), CAST( latitude AS FLOAT64 ) ),ST_GEOGFROMTEXT( 'POLYGON( (-77.02800065952454 -12.04801813888275, -77.02615529972229 -12.049612995986253, -77.0277431674591 -12.05208920270996, -77.0299747653595 -12.050788147788289, -77.02800065952454 -12.04801813888275) )' )) = true


------------------------------------------- merge
WITH input AS (
    SELECT *
    FROM (
        (
            SELECT prop.id,
                prop.link,
                CAST( prop.antiquity_years AS INT64 ) AS antiquity_years,
                prop.bedrooms,
                prop.bathrooms,
                prop.parkings,
                CAST( prop.total_area_m2 AS FLOAT64 ) AS total_area_m2,
                CAST( prop.build_area_m2 AS FLOAT64 ) AS build_area_m2,
                prop.address,
                CAST( prop.publication_date AS STRING ) AS publication_date_formated,
                CONCAT( FORMAT( "%'d", CAST( prop.dollars_price AS INT64 ) ), SUBSTR( FORMAT( "%.2f", CAST( prop.dollars_price AS FLOAT64 ) ), -3 ) ) AS dollars_price,
                CONCAT( FORMAT( "%'d", CAST( prop.others_price AS INT64 ) ), SUBSTR( FORMAT( "%.2f", CAST( prop.others_price AS FLOAT64 ) ), -3 ) )   AS others_price,
                prop.property_type,
                prop.property_new,
                prop.longitude,
                prop.latitude,
                prop.image_list,
                prop.region_id,
                r.sub_reg3,
                prop.publication_type,
                'properties' AS table
            FROM       `metal-celerity-196600.tasing_peru.properties` prop
            INNER JOIN `metal-celerity-196600.tasing_peru.regions` r
            ON         r.id = prop.region_id
            ORDER BY   id DESC
        )
        UNION ALL
        (
            SELECT prop.id,
                p.link,
                0 AS antiquity_years,
                prop.bedrooms,
                prop.bathrooms,
                prop.parkings,
                CAST( prop.total_area_m2 AS FLOAT64 ) AS total_area_m2,
                CAST( prop.build_area_m2 AS FLOAT64 ) AS build_area_m2,
                p.address,
                CAST( p.publication_date AS STRING ) AS publication_date_formated,
                CONCAT( FORMAT( "%'d", CAST( prop.dollars_price AS INT64 ) ), SUBSTR( FORMAT( "%.2f", CAST( prop.dollars_price AS FLOAT64 ) ), -3 ) ) AS dollars_price,
                CONCAT( FORMAT( "%'d", CAST( prop.others_price AS INT64 ) ), SUBSTR( FORMAT( "%.2f", CAST( prop.others_price AS FLOAT64 ) ), -3 ) ) AS others_price,
                'Proyecto' AS property_type,
                true AS property_new,
                p.longitude,
                p.latitude,
                p.image_list,
                p.region_id,
                r.sub_reg3,
                1                    AS publication_type,
                'project_properties' AS TABLE
            FROM       `metal-celerity-196600.tasing_peru.project_properties` prop
            INNER JOIN `metal-celerity-196600.tasing_peru.projects` p
            ON         p.id = prop.project_id
            INNER JOIN `metal-celerity-196600.tasing_peru.regions` r
            ON         r.id = p.region_id
            ORDER BY   id DESC
        )
    ) AS x
    WHERE     CAST( longitude AS FLOAT64 ) <= -77.026155299722
    AND       CAST( longitude AS FLOAT64 ) >= -77.029974765359
    AND       CAST( latitude AS FLOAT64 ) <= -12.048018138883
    AND       CAST( latitude AS FLOAT64 ) >= -12.05208920271
    AND       ST_WITHIN(ST_GEOGPOINT( CAST( longitude AS FLOAT64 ), CAST( latitude AS FLOAT64 ) ),ST_GEOGFROMTEXT( 'POLYGON( (-77.02800065952454 -12.04801813888275, -77.02615529972229 -12.049612995986253, -77.0277431674591 -12.05208920270996, -77.0299747653595 -12.050788147788289, -77.02800065952454 -12.04801813888275) )' )) = true

)
SELECT TO_JSON_STRING( featureCollection ) as geojson
FROM (
    SELECT 'FeatureCollection' AS type,
        ARRAY_AGG( feature ) AS features
    FROM (
        SELECT 'Feature' AS type,
        (
            SELECT STRUCT( type, coordinates )
            FROM (
                SELECT REPLACE( JSON_EXTRACT( ST_ASGeoJSON( ST_GeogPoint( CAST( longitude AS FLOAT64 ), CAST( latitude AS FLOAT64 ) ) ), '$.type' ), '"', '' ) AS type,
                    (
                        SELECT [ zero, one ]
                        FROM (
                            SELECT CAST( JSON_EXTRACT( ST_ASGeoJSON( ST_GeogPoint( CAST( longitude AS FLOAT64 ), CAST( latitude AS FLOAT64 ) ) ), '$.coordinates[0]' ) AS FLOAT64 ) AS zero,
                                CAST( JSON_EXTRACT( ST_ASGeoJSON( ST_GeogPoint( CAST( longitude AS FLOAT64 ), CAST( latitude AS FLOAT64 ) ) ), '$.coordinates[1]' ) AS FLOAT64 ) AS one
                        )
                    ) AS coordinates
            )
        ) AS geometry,
        (
            /* LIST OF PROPERTIES */
            SELECT STRUCT( id,
                property_type )
        ) AS properties
        FROM input AS outerTable
        -- WHERE
        ORDER BY properties.id DESC
        LIMIT 500
    ) AS feature
) AS featureCollection;