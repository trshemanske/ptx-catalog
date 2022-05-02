#!/usr/bin/python3

# Installing the mariadb python connector
# https://mariaprdb.com/docs/clients/mariadb-connectors/connector-python/
# sudo apt install libmariadb3 libmariadb-dev
# python3 -m pip install mariadb==1.0.10

# Module Imports
import mariadb
import sys

# Connect to MariaDB Platform
try:
    connx = mariadb.connect(
        user="ptx",
        password="ptx_db_pw",
        host="127.0.0.1",
        port=3306,
        database="ptx_catalog"
    )

# Get Cursor
    cursor = connx.cursor()

# Copy the head of the HTML file to the new output file
    with open("ptx-catalog-frame-head.html", "r") as head, open("ptx-catalog-frame.html", "w") as output:
        for line in head:
            output.write(line)
    head.close()
    output.close()

# Reopen the output file for append
    output = open("ptx-catalog-frame.html", "a")
    output.write("  <body>\n")
    output.write("    <div class=\"projects\">")

# Collect auxilliary database stuff here
# fetchall() is an easier method for grabbing all the output

# These tables are very small and convenient to store rather than polling all the time

    cursor.execute("SELECT * from levels")
#    levels_list = []
#    levels_list = [levels_list + list(x) for x in cursor]
    levels_list = cursor.fetchall()

    cursor.execute("SELECT * from phases")
#    phases_list = []
#    phases_list = [phases_list + list(x) for x in cursor]
    phases_list = cursor.fetchall()

    cursor.execute("SELECT * from features")
#    features_list = []
#    features_list = [features_list + list(x) for x in cursor]
    features_list = cursor.fetchall()

    cursor.execute("SELECT * from subjects")
#    subjects_list = []
#    subjects_list = [subjects_list + list(x) for x in cursor]
    subjects_list = cursor.fetchall()


# create_catalog_webpages():
# This was going to be a function definition, but the projects which are being converted
# or are in development are handled very differently, so I made a loop with data collection
# and presentation dependent on the development phase.

# project_class = 0 are PreTeXt projects, 1 is Converting, 2 is In Development
    for project_class in range(3):
        if project_class == 0:
            cursor.execute("SELECT * from projects WHERE (project_phase_id = '2' or project_phase_id = '3') \
                            ORDER BY project_subject_id, project_target_level_id, project_title")
        elif project_class == 1:
            cursor.execute(
                "SELECT * from projects WHERE (project_phase_id = '4') ORDER BY project_title")
        else:
            cursor.execute(
                "SELECT * from projects WHERE (project_phase_id = '1') ORDER BY project_title")

# Collect the selected project records which have been selected above
        project_data = cursor.fetchall()

# Subject and target_level tracking is used only in project_class 0 (PreTeXt projects)
        current_subject_id = -1
        current_target_level_id = -1
        current_project_count = 0

        while (current_project_count < len(project_data)):
            new_subject_id = project_data[current_project_count][9]
            new_subject_index = new_subject_id - 1
# new_subject_index is a list index (decrement by one)
            new_subject = subjects_list[new_subject_index][1]

            new_target_level_id = project_data[current_project_count][10]
            new_target_level_index = new_target_level_id - 1
# new_target_level_index is a list index (decrement by one)
            current_level = levels_list[new_target_level_index][1]

# If we are beyond the first record and there is a category change, close off previous category
# Category changes are generally based on subject, but for Mathematics, also target_level
            if project_class == 0:
                if current_subject_id >= 0:
                    if new_subject_id != current_subject_id or \
                       (new_subject_id == 1 and new_target_level_id != current_target_level_id):
                        output.write("\n     </div>   <!-- category -->\n")

# Category change --- new headers  --- format dependent upon project_class
                if new_subject_id != current_subject_id or \
                   (new_subject_id == 1 and new_target_level_id != current_target_level_id):
                    output.write("\n      <div class=\"category\">\n")

                    if new_subject_id == 1:
                        outstring = "        <span class=\"title\">{}, {}</span>\n\n"
                        output.write(outstring.format(subjects_list[new_subject_index][1],
                                                      levels_list[new_target_level_index][1]))
                    else:
                        outstring = "        <span class=\"title\">{}</span>\n\n"
                        output.write(outstring.format(
                            subjects_list[new_subject_index][1]))

                current_subject_id = new_subject_id
                current_target_level_id = new_target_level_id

            else:
                # Project being Converted or In Development
                if current_project_count == 0:
                    if project_class == 1:
                        output.write("\n      <div class=\"category\">\n")
                        output.write(
                            "        <span class=\"title\">Mature, Converting to PreTeXt</span>\n\n")
                    else:
                        output.write("\n      <div class=\"category\">\n")
                        output.write(
                            "        <span class=\"title\">In Development</span>\n\n")

# New book within category
            outstring = "        <div class=\"book-summary\" id=\"{}\">\n"
            xml_id = project_data[current_project_count][0]
            output.write(outstring.format(xml_id))

# Title of book
            output.write("          <div class=\"biblio\">\n")
            landingURL = project_data[current_project_count][12]
            projectTitle = project_data[current_project_count][1]
            projectSubTitle = project_data[current_project_count][2]
            outstring = "            <a href=\"{}\" class=\"title\"\n"
            output.write(outstring.format(landingURL))

# Subtitle if present
            if (projectSubTitle == "NULL" or projectSubTitle == "" or projectSubTitle is None):
                outstring = "               target=\"_blank\">{}</a>,\n"
                output.write(outstring.format(projectTitle))
            else:
                outstring = "               target=\"_blank\">{}: {}</a>,\n"
                output.write(outstring.format(projectTitle, projectSubTitle))

# Assemble lists of coauthors
            output.write("               <span class=\"authors\">\n")

            coauthorXMLid = project_data[current_project_count][0]
            cursor.execute(
                "SELECT * FROM coauthors WHERE coauthors_project_id=?", (coauthorXMLid,))
            coauthor_list = cursor.fetchall()

            coauthor_count = 0
            coauthor_list_length = len(coauthor_list)

            while (coauthor_count < coauthor_list_length):
                coauthor_name = coauthor_list[coauthor_count][2]
                if coauthor_count < coauthor_list_length - 1:
                    outstring = "                  {},\n"
                    output.write(outstring.format(coauthor_name))
                else:
                    outstring = "                  {}\n               </span>\n"
                    output.write(outstring.format(coauthor_name))
                coauthor_count += 1
            output.write("          </div>  <!-- biblio -->\n")

# Blurb section
            output.write(
                "\n          <div class=\"blurb\">\n             <p>\n")
            outstring = "{}\n"
            output.write(outstring.format(
                project_data[current_project_count][18]))
            outstring = "              <a data-knowl=\"\" class=\"id-ref\" data-refid=\"hk-{}-description\" title=\"Description\"> Read more</a>\n             </p>\n"
            output.write(outstring.format(xml_id))
            outstring = "              <div class=\"description-knowl\" id=\"hk-{}-description\">\n"
            output.write(outstring.format(xml_id))
            outstring = "                 <article class=\"description\">\n{}               </article>\n"

            output.write(outstring.format(
                project_data[current_project_count][19]))

            output.write(
                "              </div> <!-- description-knowl -->\n")
            output.write("          </div> <!-- blurb -->\n")

# Badges
            output.write(
                "\n          <div class=\"badges\">\n             <p>\n")
            project_recognition_code = project_data[current_project_count][21]

            cursor.execute(
                "SELECT * from recognition WHERE recognition_code=?", (project_recognition_code,))
            recognition_list = cursor.fetchall()

# There should always be at most one record returned
            if len(recognition_list) != 0:
                r_name = recognition_list[0][1]
                r_URL = recognition_list[0][2]
                outstring = "                <img class=\"badge award\" title=\"{}\" src=\"{}\"></img>"
            else:
                r_name = " "
                r_URL = " "
                outstring = "                <!--img class=\"badge award\" title=\"{}\" src=\"{}\"></img-->"
            output.write(outstring.format(r_name, r_URL))

# Features list of badges
            project_features_str = project_data[current_project_count][20]
            project_features_count = 0

            while (project_features_count < len(project_features_str)):
                if project_features_str[project_features_count:project_features_count + 1] == "1":
                    outstring = "\n                <img class=\"badge\" "
                    f_name = features_list[project_features_count][1]
                    f_URL = features_list[project_features_count][2]
# No space between <img> tags to keep badges tight
                    outstring = outstring + "title=\"{}\" src=\"{}\"></img>"
                    output.write(outstring.format(f_name, f_URL))
                project_features_count += 1

            project_license_code = project_data[current_project_count][3]
            cursor.execute(
                "SELECT * from licenses WHERE license_code=?", (project_license_code,))
            license_list = cursor.fetchall()

# There should always be exactly one record returned
            if len(license_list) != 0:
                l_name = license_list[0][1]
                l_URL = license_list[0][2]
                outstring = "\n                <img class=\"badge license\" title=\"{}\" src=\"{}\"></img>\n             </p>"
                output.write(outstring.format(l_name, l_URL))

            output.write("\n          </div>   <!-- badges -->\n")

            output.write("        </div>   <!-- book-summary -->\n")
            current_project_count += 1
# End of project_count loop

# Done processing records, so close off last category
        output.write("     </div>   <!-- category -->\n")

# End of (function) create_catalog_webpages: (for project_class in range(3) loop)

    output.write("\n   </div>   <!-- projects -->\n")

# End of projects contributions


# Catalog statistics:

    cursor.execute("SELECT project_xml_id FROM projects")
    records = cursor.fetchall()
    outstring = "\n\n   <p>Total number of projects: {}</p>\n\n\n"
    output.write(outstring.format(len(records)))

    output.write(
        "   <table>\n     <tr>\n       <td style=\"vertical-align:top;padding-right:20px;\">\n")
    for p_id in [1, 2, 3, 4, 5, 6, 7, 8, 9]:
        cursor.execute(
            "SELECT project_xml_id FROM projects WHERE project_subject_id = ?", (p_id,))
        records = cursor.fetchall()
        outstring = "          {}: {}<br />\n"
        output.write(outstring.format(
            subjects_list[p_id - 1][1], len(records)))
    output.write(
        "       </td>\n       <td style=\"vertical-align:top;padding-right:20px;\">\n")

    license_tags = ["all-rights", "CC", "GFDL", "MIT", "public", "undecided"]
    for l_id in license_tags:
        cursor.execute(
            "SELECT project_xml_id FROM projects WHERE project_license_code = ?", (l_id,))
        records = cursor.fetchall()
        cursor.execute(
            "SELECT license_name FROM licenses WHERE license_code = ?", (l_id,))
        l_name = cursor.fetchall()
        outstring = "          {}: {}<br />\n"
        output.write(outstring.format(l_name[0][0], len(records)))
    output.write(
        "       </td>\n       <td style=\"vertical-align:top;padding-right:20px;\">\n")

    for l_id in [1, 2, 3, 4, 5, 6]:
        cursor.execute(
            "SELECT project_xml_id FROM projects WHERE project_target_level_id = ?", (l_id,))
        records = cursor.fetchall()
        outstring = "          {}: {}<br />\n"
        output.write(outstring.format(levels_list[l_id - 1][1], len(records)))
    output.write("       </td>\n     </tr>\n   </table>\n")

    output.write("\n  </body>")
    output.write("\n</html>")

# Close the output file ptx-catalog-frame.html
    output.close()

# Close the mariadb connector
    cursor.close()
    connx.close()

# Mariadb error trapping
except mariadb.Error as e:
    print(f"Error connecting to MariaDB Platform: {e}")
    sys.exit(1)
