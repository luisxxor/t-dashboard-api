// Explicit creation of 'property_links' collection
db.createCollection( "property_links", {
    validator: {
        $jsonSchema: {
            bsonType: "object",
            required: [ "link" ],
            properties: {
                link: {
                    bsonType: "string",
                },
                new_link: {
                    bsonType: "bool",
                },
                scraped_at: {
                    bsonType: [ "date", "null" ],
                },
                status: {
                    bsonType: "int",
                    description: "1.-Active, 2.-Inactive"
                },
                enabled_at: {
                    bsonType: [ "date", "null" ],
                },
                disabled_at: {
                    bsonType: [ "date", "null" ],
                },
                publication_type_id: {
                    bsonType: [ "objectId" ],
                    description: "publication_types relation"
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
db.property_links.createIndex(
    { link: 1 },
    { unique: true }
)

// Explicit creation of 'project_links' collection
db.createCollection( "project_links", {
    validator: {
        $jsonSchema: {
            bsonType: "object",
            required: [ "link" ],
            properties: {
                link: {
                    bsonType: "string",
                },
                new_link: {
                    bsonType: "bool",
                },
                scraped_at: {
                    bsonType: [ "date", "null" ],
                },
                status: {
                    bsonType: "int",
                    description: "1.-Active, 2.-Inactive"
                },
                enabled_at: {
                    bsonType: [ "date", "null" ],
                },
                disabled_at: {
                    bsonType: [ "date", "null" ],
                },
                publication_type_id: {
                    bsonType: [ "objectId" ],
                    description: "publication_types relation"
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
db.project_links.createIndex(
    { link: 1 },
    { unique: true }
)

// Explicit creation of 'properties' collection
db.createCollection( "properties", {
    validator: {
        $jsonSchema: {
            bsonType: "object",
            properties: {
                link: {
                    bsonType: "string",
                },
                antiquity_years: {
                    bsonType: [ "int", "null" ],
                },
                bedrooms: {
                    bsonType: [ "double", "null" ],
                    description: "5.1 significaría '5 a más'"
                },
                bathrooms: {
                    bsonType: [ "double", "null" ],
                    description: "5.1 significaría '5 a más'"
                },
                parkings: {
                    bsonType: [ "double", "null" ],
                    description: "5.1 significaría '5 a más'"
                },
                total_area_m2: {
                    bsonType: [ "double", "null" ],
                },
                build_area_m2: {
                    bsonType: [ "double", "null" ],
                },
                terrain_area_m2: {
                    bsonType: [ "double", "null" ],
                },
                dollars_price: {
                    bsonType: [ "double", "null" ],
                },
                others_price: {
                    bsonType: [ "double", "null" ],
                },
                condo_fees: {
                    bsonType: [ "double", "null" ],
                },
                location: {
                    bsonType: [ "string", "null" ],
                },
                urbanization: {
                    bsonType: [ "string", "null" ],
                },
                reference_place: {
                    bsonType: [ "string", "null" ],
                },
                property_condition: {
                    bsonType: [ "string", "null" ],
                },
                allows_pets: {
                    bsonType: [ "bool", "null" ],
                },
                professional_use: {
                    bsonType: [ "bool", "null" ],
                },
                commercial_use: {
                    bsonType: [ "bool", "null" ],
                },
                pool: {
                    bsonType: [ "bool", "null" ],
                },
                elevator: {
                    bsonType: [ "double", "null" ],
                    description: "1.1 significaría '1 a más'"
                },
                nearby_malls: {
                    bsonType: [ "bool", "null" ],
                },
                nearby_parks: {
                    bsonType: [ "bool", "null" ],
                },
                nearby_schools: {
                    bsonType: [ "bool", "null" ],
                },
                nearby_ocean: {
                    bsonType: [ "bool", "null" ],
                },
                oceanfront: {
                    bsonType: [ "bool", "null" ],
                },
                comment_subtitle: {
                    bsonType: [ "string", "null" ],
                },
                comment_description: {
                    bsonType: [ "string", "null" ],
                },
                latitude: {
                    bsonType: [ "double", "null" ],
                },
                longitude: {
                    bsonType: [ "double", "null" ],
                },
                address: {
                    bsonType: [ "string", "null" ],
                },
                publication_date: {
                    bsonType: [ "date", "null" ],
                },
                responsable: {
                    bsonType: [ "string", "null" ],
                },
                responsable_link: {
                    bsonType: [ "string", "null" ],
                },
                publication_type_id: {
                    bsonType: [ "objectId" ],
                    description: "publication_types relation"
                },
                image_list: {
                    bsonType: [ "string", "array", "null" ],
                },
                property_new: {
                    bsonType: [ "bool", "null" ],
                },
                property_type_id: {
                    bsonType: [ "objectId", "null" ],
                },
                region_id: {
                    bsonType: [ "string", "null" ],
                },
                created_at: {
                    bsonType: "date",
                },
                updated_at: {
                    bsonType: "date",
                },

                // para propiedades de proyectos
                property_name: {
                    bsonType: [ "string", "null" ],
                },
                availability: {
                    bsonType: [ "string", "null" ],
                },
                project_id: {
                    bsonType: [ "int", "null" ],
                },
                project_phase: {
                    bsonType: [ "string", "null" ]
                },

                // for organic properties
                organic: {
                    bsonType: ["object", "null"],
                    description: "This is for organic properties",
                    properties: {
                        user_id: {
                            bsonType: ["int"],
                            description: "Id of the user that uploaded the property."
                        },
                        is_public: {
                            bsonType: ["bool"],
                            description: "Property visibility."
                        },
                        building_id: {
                            bsonType: ["string"],
                            description: "Government-related ID for the property"
                        },
                        unit: {
                            bsonType: ["string"],
                            description: "Property unit number, like apartment #08",
                        }
                    }
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
            required: [ "link" ],
            properties: {
                link: {
                    bsonType: "string",
                },
                name: {
                    bsonType: [ "string", "null" ],
                },
                project_phase: {
                    bsonType: [ "string", "null" ],
                },
                delivery_date: {
                    bsonType: [ "string", "null" ],
                },
                comment_subtitle: {
                    bsonType: [ "string", "null" ],
                },
                comment_description: {
                    bsonType: [ "string", "null" ],
                },
                pool: {
                    bsonType: [ "bool", "null" ],
                },
                elevator: {
                    bsonType: [ "double", "null" ],
                    description: "1.1 significaría '1 a más'"
                },
                responsable: {
                    bsonType: [ "string", "null" ],
                },
                responsable_link: {
                    bsonType: [ "string", "null" ],
                },
                floors: {
                    bsonType: [ "int", "null" ],
                },
                location: {
                    bsonType: [ "string", "null" ],
                },
                urbanization: {
                    bsonType: [ "string", "null" ],
                },
                reference_place: {
                    bsonType: [ "string", "null" ],
                },
                latitude: {
                    bsonType: [ "double", "null" ],
                },
                longitude: {
                    bsonType: [ "double", "null" ],
                },
                address: {
                    bsonType: [ "string", "null" ],
                },
                publication_date: {
                    bsonType: [ "date", "null" ],
                },
                dollars_price: {
                    bsonType: [ "double", "null" ],
                },
                others_price: {
                    bsonType: [ "double", "null" ],
                },
                project_type: {
                    bsonType: [ "string", "null" ]
                },
                publication_type_id: {
                    bsonType: [ "objectId" ],
                    description: "publication_types relation"
                },
                image_list: {
                    bsonType: [ "string", "array", "null" ],
                },
                region_id: {
                    bsonType: [ "string", "null" ],
                },
                created_at: {
                    bsonType: "date",
                },
                updated_at: {
                    bsonType: "date",
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


// Explicit creation of 'regions' collection
db.createCollection( "regions", {
    validator: {
        $jsonSchema: {
            bsonType: "object",
            required: [ "country" ],
            properties: {
                country: {
                    bsonType: "string",
                },
                sub_reg1: {
                    bsonType: "string",
                },
                sub_reg2: {
                    bsonType: "string",
                },
                sub_reg3: {
                    bsonType: "string",
                },
                sub_reg4: {
                    bsonType: "string",
                },
                sub_reg5: {
                    bsonType: "string",
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
            required: [ "owner_name", "name" ],
            properties: {
                name: {
                    bsonType: "string",
                },
                owner_name: {
                    bsonType: "string",
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

// Explicit creation of 'clients' collection
db.createCollection( "clients", {
    validator: {
        $jsonSchema: {
            bsonType: "object",
            required: [ "user_id", "client_name", "executive", "contact" ],
            properties: {
                user_id: {
                    bsonType: ["int"]
                },
                entity_id: {
                    bsonType: ["string", "null"]
                },
                client_name: {
                    bsonType: ["string"]
                },
                executive: {
                    bsonType: ["object"],
                    properties: {
                        name: {
                            bsonType: ["string", "null"]
                        },
                        phone: {
                            bsonType: ["string", "null"]
                        },
                        email: {
                            bsonType: ["string", "null"]
                        },
                    }
                },
                contact: {
                    bsonType: ["object"],
                    properties: {
                        name: {
                            bsonType: ["string", "null"]
                        },
                        phone: {
                            bsonType: ["string", "null"]
                        },
                        email: {
                            bsonType: ["string", "null"]
                        },
                    }
                }
            }
        },
    },
    validationLevel: "strict",
    validationAction: "error"
} )


// Explicit creation of 'tracings' collection
db.createCollection( "tracings", {
    validator: {
        $jsonSchema: {
            bsonType: "object",
            required: [ "user_id", "status" ],
            properties: {
                user_id: {
                    bsonType: ["int"]
                },
                client_id: {
                    bsonType: ["objectId", "null"]
                },
                property_id: {
                    bsonType: ["objectId", "null"]
                },
                delivery: {
                    bsonType: ["object"],
                    properties: {
                        quantity: {
                            bsonType: ["int", "null"]
                        },
                        unit: {
                            enum: ["horas", "semanas", "null" ]
                        }
                    }
                },
                job_type: {
                    bsonType: ['string']
                },
                objective: {
                    bsonType: ['string']
                },
                operation_type: {
                    bsonType: ['string']
                },
                status: {
                    bsonType: ['string']
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

// Explicit creation of 'job_types' collection
db.createCollection( "job_types", {
    validator: {
        $jsonSchema: {
            bsonType: "object",
            required: ['slug', 'label'],
            properties: {
                slug: {
                    bsonType: ["string"]
                },
                label: {
                    bsonType: ["string"]
                },
                description: {
                    bsonType: ["string"]
                }
            }
        },
    },
    validationLevel: "strict",
    validationAction: "error"
} )

db.job_types.createIndex(
    { slug: 'text' }
)

// Explicit creation of 'objectives' collection
db.createCollection( "objectives", {
    validator: {
        $jsonSchema: {
            bsonType: "object",
            required: ['slug', 'label'],
            properties: {
                slug: {
                    bsonType: ["string"]
                },
                label: {
                    bsonType: ["string"]
                },
                description: {
                    bsonType: ["string"]
                }
            }
        },
    },
    validationLevel: "strict",
    validationAction: "error"
} )

db.objectives.createIndex(
    { slug: 'text' }
)

// Explicit creation of 'operation_types' collection
db.createCollection( "operation_types", {
    validator: {
        $jsonSchema: {
            bsonType: "object",
            required: ['slug', 'label'],
            properties: {
                slug: {
                    bsonType: ["string"]
                },
                label: {
                    bsonType: ["string"]
                },
                description: {
                    bsonType: ["string"]
                }
            }
        },
    },
    validationLevel: "strict",
    validationAction: "error"
} )

db.operation_types.createIndex(
    { slug: 'text' }
)

// Explicit creation of 'statuses' collection
db.createCollection( "statuses", {
    validator: {
        $jsonSchema: {
            bsonType: "object",
            required: ['slug', 'label'],
            properties: {
                slug: {
                    bsonType: ["string"]
                },
                label: {
                    bsonType: ["string"]
                },
                description: {
                    bsonType: ["string"]
                }
            }
        },
    },
    validationLevel: "strict",
    validationAction: "error"
} )

db.statuses.createIndex(
    { slug: 'text' }
)
