read -r -d '' QUERY << EOM
{
    "query": {
        "bool": {
        }
    },
    "sort": { "timestamp": "asc" } 
}
EOM
echo $QUERY
elasticdump \
  --input=http://idea-db.isae.fr:9200/xapi_adn_enriched \
  --output=$ \
  --searchBody="$QUERY" | gzip > 1MAE806.json.gz
#elasticdump \
#  --input=http://idea-db.isae.fr:9200/xapi_statements \
#  --output=$ \
#  --searchBody='{"query": { "range": { "timestamp": { "gte": "2020-05-13T00:00:00" } } }, "sort": { "timestamp": "asc" } }' \
#| gzip > xapi_statements.json.gz
#elasticdump \
#  --input=http://idea-db.isae.fr:9200/xapi_adn_statements \
#  --output=$ \
#  --searchBody='{"query": { "range": { "timestamp": { "gte": "2019-09-01T00:00:00" } } }, "sort": { "timestamp": "asc" } }' \
#| gzip > xapi_adn_statements.json.gz


