CURRENT_VERSION=$(echo "6.0")
PARTS=(`echo ${CURRENT_VERSION} | tr '.' ' '`)
if [ '9' = ${PARTS[1]} ]; then
  NEW_TEST_VERSION=$(echo $(( ${PARTS[0]} + 1 ))\\.0)
else
  NEW_TEST_VERSION=$(echo ${PARTS[0]}\\.$(( ${PARTS[1]} + 1 )))
fi

NEW_TEST=$(echo "${NEW_TEST_VERSION}-(alpha" | sed 's|\\|\\\\|g')
CURRENT_TEST=$(echo "${CURRENT_VERSION}-(alpha" | sed 's|\.|\\.|g')
#CURRENT_TEST=$(grep -m 1 "[[:digit:]]+\\\.[[:digit:]]+-\(alpha" v1/migration/index.php | sed 's|\\|\\\\|g')

echo ${CURRENT_VERSION}
echo ${NEW_TEST_VERSION}
echo ${NEW_TEST}
echo ${CURRENT_TEST}