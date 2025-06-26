/**
 * Main Function
 *
 *
 *
 */
(function ($) {
  "use strict";
  $(document).ready(() => {
    new Certificate();
  });
})(jQuery);

/**
 * Constants
 *
 *
 *
 */
// =============================
//  PDF METADATA
// =============================
const PDF_AUTHOR = "NEOnet (Northeast Ohio Network for Educational Technology)";
const PDF_SUBJECT = "NEOnet Event CEU Certificate";
const PDF_CREATOR = "NEOnet Website";
const PDF_KEYWORDS =
  "ceu, continuing education, professional development, certificate";

// =============================
//  PDF CONFIGURATIONS
// =============================
const ORIENTATION = "landscape";
const HEIGHT = 210;
const WIDTH = 297;
const FONT = "times";
const FONT_SIZE = 14;
const LINE_SPACING = 15;
const LABEL_COLUMN = [50, 75];
const VALUE_COLUMN = [125, 75];
const LABEL_COLOR = [27, 54, 99];
const VALUE_COLOR = [0, 0, 0];

// =============================
//  MAP TO EVENTS DATA
// =============================
const LABEL_MAP = {
  user: "Certificate is Awarded to:",
  ceu: "CEU:",
  title: "Title of Course:",
  description: "Course Description:",
  date: "Date Completed:",
};

const CATEGORY_MAP = {
  1: "ed_tech",
  2: "fiscal",
  3: "governance",
  4: "student_services",
  5: "tech_services",
  6: "hybrid_training",
  7: "emis",
};

const IMAGE_MAP = {
  ed_tech:
    "http://events-connector.local/wp-content/uploads/2025/05/certificate.jpg",
  fiscal:
    "http://events-connector.local/wp-content/uploads/2025/05/certificate.jpg",
  student_services:
    "http://events-connector.local/wp-content/uploads/2025/05/student-certificate.jpg",
  default:
    "https://neonet.org/wp-content/uploads/2022/02/Wordpress-Certificate-Background.png",
};

// =============================
//  PDF TEMPLATES
// =============================
const TEMPLATES = [
  {
    id: "student_services",
    backgroundImage:
      "http://events-connector.local/wp-content/uploads/2025/05/student-certificate.jpg",
    textElements: [
      { key: "user", hideLabel: true, x: 125, y: 72 },
      { key: "title", x: 130, y: 100 },
      { key: "ceu", x: 130, y: 110 },
      { key: "description", x: 130, y: 120 },
      { key: "date", hideLabel: true, x: 225, y: 170 },
    ],
  },
  {
    id: "emis",
    backgroundImage:
      "http://events-connector.local/wp-content/uploads/2025/05/emis-certificate.jpg",
    textElements: [
      { key: "user", hideLabel: true, x: 125, y: 72 },
      { key: "title", x: 130, y: 100 },
      { key: "ceu", x: 130, y: 110 },
      { key: "description", x: 130, y: 120 },
      { key: "date", hideLabel: true, x: 225, y: 170 },
    ],
  },
  {
    id: "fiscal",
    backgroundImage:
      "http://events-connector.local/wp-content/uploads/2025/05/default-certificate.png",
    textElements: [
      { key: "user" },
      { key: "ceu" },
      { key: "title" },
      { key: "description" },
      { key: "date" },
    ],
  },
  {
    id: "ed_tech",
    backgroundImage:
      "http://events-connector.local/wp-content/uploads/2025/05/default-certificate.png",
    textElements: [
      { key: "user" },
      { key: "ceu" },
      { key: "title" },
      { key: "description" },
      { key: "date" },
    ],
  },
  {
    id: "ed_tech_tamra",
    backgroundImage:
      "http://events-connector.local/wp-content/uploads/2025/05/tamra-certificate.webp",
    textElements: [
      { key: "user" },
      { key: "ceu" },
      { key: "title" },
      { key: "description" },
      { key: "date" },
    ],
  },
  {
    id: "ed_tech_daniel",
    backgroundImage:
      "http://events-connector.local/wp-content/uploads/2025/05/certificate.jpg",
    textElements: [
      { key: "user" },
      { key: "ceu" },
      { key: "title" },
      { key: "description" },
      { key: "date" },
    ],
  },
  {
    id: "ed_tech_julia",
    backgroundImage:
      "http://events-connector.local/wp-content/uploads/2025/05/julia-certificate.png",
    textElements: [
      { key: "user" },
      { key: "ceu" },
      { key: "title" },
      { key: "description" },
      { key: "date" },
    ],
  },
  {
    id: "default",
    backgroundImage:
      "http://events-connector.local/wp-content/uploads/2025/05/default-certificate.png",
    textElements: [
      { key: "user" },
      { key: "ceu" },
      { key: "title" },
      { key: "description" },
      { key: "date" },
    ],
  },
];

/**
 * Certificate Class
 *
 *
 *
 */
class Certificate {
  // Constants
  PDF_AUTHOR = "NEOnet (Northeast Ohio Network for Educational Technology)";
  PDF_SUBJECT = "NEOnet Event CEU Certificate";
  PDF_CREATOR = "NEOnet Website";
  PDF_KEYWORDS =
    "ceu, continuing education, professional development, certificate";
  ORIENTATION = "landscape";
  HEIGHT = 210;
  WIDTH = 297;
  FONT = "times";
  FONT_SIZE = 14;
  LINE_SPACING = 15;
  DEFAULT_VALUE_OFFSET = [125, 75];
  DEFAULT_LABEL_OFFSET = [50, 75];
  LABEL_COLOR = [27, 54, 99];
  VALUE_COLOR = [0, 0, 0];
  LABEL_MAP = {
    user: "Certificate is Awarded to:",
    ceu: "CEU:",
    title: "Title of Course:",
    description: "Course Description:",
    date: "Date Completed:",
  };

  // Certificate Data
  data = null;
  currentUser = null;
  redirectUrl = null;
  doc = null;

  constructor() {
    // Ensure the proper javascript libraries loaded onto window global
    this.verifyWindowGlobals();

    // Store data from localized PHP variables
    this.getGlobalVariables();

    // Initialize jsPDF object to generate PDF
    this.initializePDFCreator();

    // Generate the PDF
    this.generatePDF();
  }

  /**
   * Verify Window Globals
   *
   * Ensure the proper variables are loaded in the global environment
   * to be used throught script
   */
  verifyWindowGlobals() {
    if (!window.jspdf)
      throw new Error("jsPDF must be available as a global on the window.");

    if (!window.neonetCertificate)
      throw new Error(
        "Localized WordPress variable 'neonetCertificate' must be available as a global on the window."
      );
  }

  /**
   * Get Global Variables
   *
   * Retrieve the global variables from the window and delete them for
   * future safe-keeping
   */
  getGlobalVariables() {
    const { data, currentUser, redirectUrl } = window.neonetCertificate;

    this.data = data;
    this.currentUser = currentUser;
    this.redirectUrl = redirectUrl;
  }

  /**
   * Initialize PDF Creator
   *
   * Instantiate a PDF Creator object from the jsPDF library
   * Set font style and font size
   * Set Metadata
   */
  initializePDFCreator() {
    const { jsPDF } = window.jspdf;

    this.doc = new jsPDF({
      orientation: this.ORIENTATION,
    });

    this.doc.setFont(this.FONT);
    this.doc.setFontSize(this.FONT_SIZE);

    this.doc.setProperties({
      title: this.data.title,
      subject: this.PDF_SUBJECT,
      author: this.PDF_AUTHOR,
      keywords: this.PDF_KEYWORDS,
      creator: this.PDF_CREATOR,
      created: new Date(),
    });
  }

  /**
   * Generate PDF
   *
   * Handle the logic necessary to generate the PDF
   */
  generatePDF() {
    const template = this.getTemplate(this.data.category);

    this.loadBackgroundImage(template.backgroundImage, (image) => {
      // Set background image for PDF
      this.setBackgroundImage(image);

      // Print out text based on template
      this.printText(template.textElements);

      // Save PDF
      this.save();

      // Display PDF
      this.displayPDF();

      // Redirect Back to Certificates Page
      this.redirectBackToCertificates();
    });
  }

  /**
   * Load Background Image
   *
   * Asynchronous Image load that handles a callback function.
   * Used as a wrapper around the rest of the PDF Generation.
   */
  loadBackgroundImage(url, callback) {
    const img = new Image();

    img.onError = function () {
      alert('Cannot load image: "' + url + '"');
    };

    img.onload = function () {
      callback(img);
    };

    img.src = url;
  }

  /**
   * Set Backround Image
   *
   * Utility method to set background image of PDF
   */
  setBackgroundImage(image) {
    this.doc.addImage(
      image,
      "JPEG",
      0,
      0,
      this.WIDTH,
      this.HEIGHT,
      "cert-background"
    );
  }

  /**
   * Print Text
   *
   * Utility method to print text to PDF
   */
  printText(elements) {
    for (const element of elements) {
      this.doc.setTextColor(...element.color);
      this.doc.text(element.x, element.y, element.text);
    }
  }

  /**
   *
   * @param {{
   *    key: string,
   *    hideLabel: boolean,
   *    x: number,
   *    y: number
   * }[]} values
   */
  getTextElements(values) {
    const elements = [];

    let currentLine = 0;
    for (const item of values) {
      // Check if label hidden
      if (!item.hideLabel) {
        // Get Label Coordinates
        const labelX = item.x ? item.x - 75 : this.DEFAULT_LABEL_OFFSET[0];
        const labelY =
          item.y ??
          this.DEFAULT_LABEL_OFFSET[1] + this.LINE_SPACING * currentLine;

        // Create Label
        elements.push({
          x: labelX,
          y: labelY,
          text: this.LABEL_MAP[item.key],
          color: this.LABEL_COLOR,
        });
      }

      // Get Value Coordinates
      const valueX = item.x ?? this.DEFAULT_VALUE_OFFSET[0];
      const valueY =
        item.y ??
        this.DEFAULT_VALUE_OFFSET[1] + this.LINE_SPACING * currentLine;

      // Format and process text value
      let text = this.data[item.key]?.toString();
      if (item.key === "description" && !!this.data[item.key]) {
        text = this.doc.splitTextToSize(text, 120);
      }

      // Create Value
      elements.push({
        x: valueX,
        y: valueY,
        text: item.key === "user" ? this.currentUser.display_name : text,
        color: this.VALUE_COLOR,
      });

      // Increment the line
      currentLine++;
    }

    return elements;
  }

  getTemplate(category) {
    let template = null;
    if (category === "ed_tech") {
      template = TEMPLATES.find((template) =>
        template.id.includes(this.data.host.split(" ")[0].toLowerCase())
      );

      if (!template) {
        template = TEMPLATES.find((template) => template.id === "ed_tech");
      }
    } else {
      template = TEMPLATES.find((template) => template.id === category);

      if (!template) {
        template = TEMPLATES.find((template) => template.id === "default");
      }
    }

    return this.getTemplateReturnValue(template);
  }

  getTemplateReturnValue(template) {
    return {
      backgroundImage: template.backgroundImage,
      textElements: this.getTextElements(template.textElements),
    };
  }

  /**
   * Generate Filename
   *
   * Utility method to format and generate file name based on certificate
   * data.
   *
   * Format: <yyyy-mm-dd>-CERTIFICATE-<event-title>.pdf
   */
  generateFileName() {
    const formattedDate = new Date(this.data.date).toISOString().split("T")[0];
    const formattedTitle = this.data.title.toLowerCase().replaceAll(" ", "-");

    return `${formattedDate}-CERTIFICATE-${formattedTitle}.pdf`;
  }

  /**
   * Save File
   *
   */
  save() {
    const filename = this.generateFileName();

    this.doc.save(filename);
  }

  /**
   * Display PDF
   *
   */
  displayPDF() {
    // const string = this.doc.output("datauristring");
    this.doc.output("pdfobjectnewwindow");
  }

  /**
   * Redirect Back to Certificates
   *
   */
  redirectBackToCertificates() {
    window.location.replace(this.redirectUrl);
  }
}
