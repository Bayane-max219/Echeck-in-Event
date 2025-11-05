import 'package:flutter/material.dart';
import 'dart:convert';
import 'package:http/http.dart' as http;

const String baseUrl = 'http://10.0.2.2:8000'; // À adapter selon l'environnement

class RecentScansScreen extends StatefulWidget {
  const RecentScansScreen({Key? key}) : super(key: key);

  @override
  State<RecentScansScreen> createState() => _RecentScansScreenState();
}

class _RecentScansScreenState extends State<RecentScansScreen> {
  late Future<List<CheckInItem>> _futureScans;

  @override
  void initState() {
    super.initState();
    // Remplacer 1 par l'eventId réel si besoin
    _futureScans = fetchRecentScans(eventId: 1);
  }

  Future<List<CheckInItem>> fetchRecentScans({required int eventId}) async {
    final response = await http.get(Uri.parse('$baseUrl/api/events/$eventId/checkins'));
    if (response.statusCode == 200) {
      final data = json.decode(response.body);
      final List scans = data['checkIns'] ?? [];
      return scans.map((e) => CheckInItem.fromJson(e)).toList();
    } else {
      throw Exception('Failed to load scans');
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Recent Scans')),
      body: FutureBuilder<List<CheckInItem>>(
        future: _futureScans,
        builder: (context, snapshot) {
          if (snapshot.connectionState == ConnectionState.waiting) {
            return const Center(child: CircularProgressIndicator());
          } else if (snapshot.hasError) {
            return Center(child: Text('Error: \\${snapshot.error}'));
          } else if (!snapshot.hasData || snapshot.data!.isEmpty) {
            return const Center(child: Text('No scans yet.'));
          }
          final scans = snapshot.data!;
          return ListView.separated(
            itemCount: scans.length,
            separatorBuilder: (_, __) => const Divider(height: 1),
            itemBuilder: (context, i) {
              final scan = scans[i];
              return ListTile(
                leading: const Icon(Icons.qr_code_scanner),
                title: Text(scan.participantName),
                subtitle: Text('Date: \\${scan.checkedInAt}\nNotes: \\${scan.notes ?? '-'}'),
              );
            },
          );
        },
      ),
    );
  }
}

class CheckInItem {
  final String participantName;
  final String checkedInAt;
  final String? notes;

  CheckInItem({required this.participantName, required this.checkedInAt, this.notes});

  factory CheckInItem.fromJson(Map<String, dynamic> json) {
    final participant = json['participant'] ?? {};
    return CheckInItem(
      participantName: participant['firstName'] != null && participant['lastName'] != null
          ? '${participant['firstName']} ${participant['lastName']}'
          : (participant['nom'] ?? 'Unknown'),
      checkedInAt: json['checkedInAt'] ?? '-',
      notes: json['notes'],
    );
  }
}
