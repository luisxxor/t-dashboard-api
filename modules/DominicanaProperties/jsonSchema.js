// Explicit creation of 'property_links' collection
db.createCollection( "property_links", {
    validator: {
        $jsonSchema: {
            bsonType: "object",
            required: [
                "webpage",
                "link",
                "new_link",
                "property_type_id",
                "publication_type_id",
                "status",
                "created_at",
                "scraped_at",
                "enabled_at",
                "disabled_at"
            ],
            properties: {
                webpage: {
                    bsonType: "string"
                },
                link: {
                    bsonType: "string"
                },
                new_link: {
                    bsonType: "bool"
                },
                property_type_id: {
                    bsonType: "objectId"
                },
                publication_type_id: {
                    bsonType: "objectId"
                },
                status: {
                    bsonType: "bool"
                },
                created_at: {
                    bsonType: [ "date", "null" ]
                },
                scraped_at: {
                    bsonType: [ "date", "null" ]
                },
                enabled_at: {
                    bsonType: [ "date", "null" ]
                },
                disabled_at: {
                    bsonType: [ "date", "null" ]
                }
            }
        },
    },
    validationLevel: "strict",
    validationAction: "error"
} )
db.property_links.createIndex(
    { link: 1 },
    { unique: true }
)

// Explicit creation of 'project_links' collection
db.createCollection( "project_links", {
    validator: {
        $jsonSchema: {
            bsonType: "object",
            required: [
                "webpage",
                "link",
                "new_link",
                "property_type_id",
                "publication_type_id",
                "status",
                "created_at",
                "scraped_at",
                "enabled_at",
                "disabled_at"
            ],
            properties: {
                webpage: {
                    bsonType: "string"
                },
                link: {
                    bsonType: "string"
                },
                new_link: {
                    bsonType: "bool"
                },
                property_type_id: {
                    bsonType: "objectId"
                },
                publication_type_id: {
                    bsonType: "objectId"
                },
                status: {
                    bsonType: "bool"
                },
                created_at: {
                    bsonType: [ "date", "null" ]
                },
                scraped_at: {
                    bsonType: [ "date", "null" ]
                },
                enabled_at: {
                    bsonType: [ "date", "null" ]
                },
                disabled_at: {
                    bsonType: [ "date", "null" ]
                }
            }
        },
    },
    validationLevel: "strict",
    validationAction: "error"
} )
db.project_links.createIndex(
    { link: 1 },
    { unique: true }
)

// Explicit creation of 'properties' collection
db.createCollection( "properties", {
    validator: {
        $jsonSchema: {
            bsonType: "object",
            required: [
                "webpage",
                "link",
                "property_type_id",
                "publication_type_id",
                "total_area_m2",
                "build_area_m2",
                "bedrooms",
                "bathrooms",
                "parkings",
                "toilette",
                "floors",
                "condition",
                "dollars_price",
                "others_price",
                "rental_price",
                "is_new",
                "project_id",
                "responsable",
                "phone_list",
                "publication_date",
                "comment_description",
                "extra_fields",
                "longitude",
                "latitude",
                "geo_location",
                "address",
                "region_id",
                "temp_images",
                "image_list",
                "metadata",,
                "status",
                "created_at",
                "updated_at",
                "enabled_at",
                "disabled_at"
            ],
            properties: {
                webpage: {
                    bsonType: "string",
                },
                link: {
                    bsonType: "string",
                },
                property_type_id: {
                    bsonType: "objectId"
                },
                publication_type_id: {
                    bsonType: "objectId"
                },
                total_area_m2: {
                    bsonType: "double"
                },
                build_area_m2: {
                    bsonType: "double"
                },
                bedrooms: {
                    bsonType: [ "double", "null" ]
                },
                bathrooms: {
                    bsonType: [ "double", "null" ]
                },
                parkings: {
                    bsonType: "double"
                },
                toilette: {
                    bsonType: "double"
                },
                floors: {
                    bsonType: "double"
                },
                condition: {
                    bsonType: [ "string", "null" ]
                },
                dollars_price: {
                    bsonType: "double"
                },
                others_price: {
                    bsonType: "double"
                },
                rental_price: {
                    bsonType: "double"
                },
                publication_date: {
                    bsonType: "date"
                },
                is_new: {
                    bsonType: "bool",
                },
                project_id: {
                    bsonType: "int",
                },
                responsable: {
                    bsonType: [ "string", "null" ]
                },
                phone_list: {
                    bsonType: "array"
                },
                comment_description: {
                    bsonType: [ "string", "null" ]
                },
                address: {
                    bsonType: [ "string", "null" ]
                },
                region_id: {
                    bsonType: "objectId"
                },
                latitude: {
                    bsonType: "double",
                },
                longitude: {
                    bsonType: "double",
                },
                geo_location: {
                    bsonType: "object",
                },
                extra_fields: {
                    bsonType: [ "array", "object" ],
                },
                image_list: {
                    bsonType: "array",
                },
                temp_images: {
                    bsonType: "array",
                },
                metadata: {
                    bsonType: "array"
                },
                status: {
                    bsonType: "bool"
                },
                created_at: {
                    bsonType: [ "date", "null" ],
                },
                updated_at: {
                    bsonType: [ "date", "null" ]
                },
                enabled_at: {
                    bsonType: [ "date", "null" ]
                },
                disabled_at: {
                    bsonType: [ "date", "null" ]
                }
            }
        },
    },
    validationLevel: "strict",
    validationAction: "error"
} )
db.properties.createIndex(
    { link: 1 },
    { unique: true }
)
db.properties.createIndex( { geo_location: "2dsphere" } )

// Explicit creation of 'projects' collection
db.createCollection( "projects", {
    validator: {
        $jsonSchema: {
            bsonType: "object",
            required: [
                "webpage",
                "link",
                "property_type_id",
                "publication_type_id",
                "dollars_price",
                "others_price",
                "bedrooms",
                "bathrooms",
                "parkings",
                "toilette",
                "address",
                "total_area_m2",
                "build_area_m2",
                "floors",
                "delivery_date",
                "responsable",
                "condition",
                "publication_date",
                "comment_description",
                "phone_list",
                "extra_fields",
                "region_id",
                "temp_images",
                "image_list",
                "metadata",
                "status",
                "created_at",
                "updated_at",
                "enabled_at",
                "disabled_at"
            ],
            properties: {
                webpage: {
                    bsonType: "string",
                },
                link: {
                    bsonType: "string",
                },
                property_type_id: {
                    bsonType: "objectId",
                },
                publication_type_id: {
                    bsonType: "objectId"
                },
                dollars_price: {
                    bsonType: "double"
                },
                others_price: {
                    bsonType: "double"
                },
                bedrooms: {
                    bsonType: "double"
                },
                bathrooms: {
                    bsonType: "double"
                },
                parkings: {
                    bsonType: "double"
                },
                toilette: {
                    bsonType: "double"
                },
                latitude: {
                    bsonType: "double"
                },
                longitude: {
                    bsonType: "double"
                },
                address: {
                    bsonType: [ "string", "null" ]
                },
                geo_location: {
                    bsonType: [ "array", "object" ]
                },
                responsable: {
                    bsonType: [ "string", "null" ]
                },
                total_area_m2: {
                    bsonType: "double"
                },
                build_area_m2: {
                    bsonType: "double"
                },
                floors: {
                    bsonType: "double"
                },
                condition: {
                    bsonType: [ "string", "null" ]
                },
                delivery_date: {
                    bsonType: "date"
                },
                publication_date: {
                    bsonType: "date"
                },
                comment_description: {
                    bsonType: [ "string", "null" ]
                },
                phone_list: {
                    bsonType: "array"
                },
                extra_fields: {
                    bsonType: [ "array", "object" ]
                },
                region_id: {
                    bsonType: "objectId"
                },
                temp_images: {
                    bsonType: "array"
                },
                image_list: {
                    bsonType: "array"
                },
                metadata: {
                    bsonType: "array"
                },
                status: {
                    bsonType: "bool"
                },
                created_at: {
                    bsonType: [ "date", "null" ],
                },
                updated_at: {
                    bsonType: [ "date", "null" ]
                },
                enabled_at: {
                    bsonType: [ "date", "null" ]
                },
                disabled_at: {
                    bsonType: [ "date", "null" ]
                }
            }
        },
    },
    validationLevel: "strict",
    validationAction: "error"
} )
db.projects.createIndex(
    { link: 1 },
    { unique: true }
)
db.projects.createIndex( { geo_location: "2dsphere" } )

// Explicit creation of 'regions' collection
db.createCollection( "regions", {
    validator: {
        $jsonSchema: {
            bsonType: "object",
            required: [
                "country",
                "sub_reg1",
                "sub_reg2",
                "sub_reg3",
                "sub_reg4",
                "sub_reg5"
            ],
            properties: {
                country: {
                    bsonType: "string"
                },
                sub_reg1: {
                    bsonType: [ "string", "null" ]
                },
                sub_reg2: {
                    bsonType: [ "string", "null" ]
                },
                sub_reg3: {
                    bsonType: [ "string", "null" ]
                },
                sub_reg4: {
                    bsonType: [ "string", "null" ]
                },
                sub_reg5: {
                    bsonType: [ "string", "null" ]
                }
            }
        },
    },
    validationLevel: "strict",
    validationAction: "error"
} )

// Explicit creation of 'property_types' collection
db.createCollection( "property_types", {
    validator: {
        $jsonSchema: {
            bsonType: "object",
            required: [ "item", "name", "webpage" ],
            properties: {
                item: {
                    bsonType: "string"
                },
                name: {
                    bsonType: "string"
                },
                webpage: {
                    bsonType: "string"
                }
            }
        },
    },
    validationLevel: "strict",
    validationAction: "error"
} )
db.property_types.createIndex(
    { name: 'text' }
)

// Explicit creation of 'publication_types' collection
db.createCollection( "publication_types", {
    validator: {
        $jsonSchema: {
            bsonType: "object",
            required: [ "item", "name", "webpage" ],
            properties: {
                item: {
                    bsonType: "string"
                },
                name: {
                    bsonType: "string"
                },
                webpage: {
                    bsonType: "string"
                }
            }
        },
    },
    validationLevel: "strict",
    validationAction: "error"
} )

// Explicit creation of 'searches' collection
db.createCollection( "searches", {
    validator: {
        $jsonSchema: {
            bsonType: "object",
            required: [ "user_id", "metadata", "created_at" ],
            properties: {
                user_id: {
                    bsonType: "int",
                },
                metadata: {
                    bsonType: "object",
                },
                "metadata.vertices": {
                    bsonType: "array",
                },
                "metadata.filters": {
                    bsonType: "object",
                },
                "metadata.initPoint.address": {
                    bsonType: "string",
                },
                "metadata.initPoint.lat": {
                    bsonType: "double",
                },
                "metadata.initPoint.lng": {
                    bsonType: "double",
                },
                "metadata.quantity": {
                    bsonType: "int",
                },
                created_at: {
                    bsonType: "date",
                }
            }
        },
    },
    validationLevel: "strict",
    validationAction: "error"
} )
db.searches.createIndex(
    { user_id: 1 }
)
db.searches.createIndex(
    { created_at: 1 },
    { expireAfterSeconds: 21600 } // 6 horas
)
